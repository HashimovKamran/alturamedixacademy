<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $folders = $this->folders();
        $allFiles = $this->scanFiles();
        $files = $this->filterFiles($allFiles, $request);

        return view('admin.media.index', [
            'folders' => $folders,
            'files' => $files,
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'folder' => (string) $request->query('folder', ''),
                'type' => (string) $request->query('type', ''),
            ],
            'stats' => [
                'totalFiles' => count($allFiles),
                'totalImages' => count(array_filter($allFiles, fn (array $file): bool => $file['is_image'])),
                'totalDocs' => count(array_filter($allFiles, fn (array $file): bool => ! $file['is_image'])),
                'totalSize' => array_sum(array_map(fn (array $file): int => $file['size'], $allFiles)),
            ],
        ]);
    }

    public function store(Request $request, AdminLogService $logs): RedirectResponse
    {
        $folder = $this->cleanFolder((string) $request->input('folder', 'media'));
        $uploaded = 0;

        try {
            $files = $request->file('media_files', []);
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $path = $this->storeFile($file, $folder);
                $uploaded++;
                $logs->write($request, 'media', 'upload', 'Media fayl yükləndi: ' . $path, 'media_file');
            }

            if ($uploaded === 0) {
                return back()->withErrors(['media_files' => 'Yükləmək üçün fayl seçilməyib.'])->withInput();
            }

            return back()->with('status', $uploaded . ' fayl uğurla yükləndi.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['media_files' => $exception->getMessage()])->withInput();
        }
    }

    public function destroy(Request $request, AdminLogService $logs): RedirectResponse
    {
        try {
            $relativePath = $this->safeRelativePath((string) $request->input('file_path', ''));
            $fullPath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
            $realFullPath = realpath($fullPath);
            $realUploadsRoot = realpath(public_path('uploads'));

            if (! $realFullPath || ! $realUploadsRoot || ! str_starts_with($realFullPath, $realUploadsRoot)) {
                throw new RuntimeException('Fayl yolu təhlükəsiz deyil.');
            }

            if (! is_file($realFullPath)) {
                throw new RuntimeException('Fayl tapılmadı.');
            }

            unlink($realFullPath);
            $logs->write($request, 'media', 'delete', 'Media fayl silindi: ' . $relativePath, 'media_file');

            return back()->with('status', 'Fayl silindi.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file_path' => $exception->getMessage()]);
        }
    }

    private function folders(): array
    {
        return [
            'media' => 'Ümumi media',
            'sliders' => 'Slider',
            'articles' => 'Məqalələr',
            'categories' => 'Kateqoriyalar',
            'gallery' => 'Qalereya',
            'pages' => 'Səhifələr',
            'blocks' => 'Ana səhifə blokları',
            'partners' => 'Tərəfdaşlar',
            'ads' => 'Reklamlar',
            'settings' => 'Sayt ayarları',
            'certificates' => 'Sertifikatlar',
            'documents' => 'Sənədlər',
            'page_builder' => 'Səhifə qurucusu',
            'visual' => 'Vizual redaktor',
        ];
    }

    private function storeFile(UploadedFile $file, string $folder): string
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Fayl yüklənmədi: ' . $file->getClientOriginalName());
        }

        if ($file->getSize() > 25 * 1024 * 1024) {
            throw new RuntimeException('Fayl maksimum 25 MB ola bilər: ' . $file->getClientOriginalName());
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $this->allowedExtensions(), true)) {
            throw new RuntimeException('Bu fayl tipinə icazə verilmir: ' . $file->getClientOriginalName());
        }

        if ($extension === 'svg') {
            $content = file_get_contents($file->getRealPath());
            if ($content === false || stripos($content, '<script') !== false || stripos($content, 'onload=') !== false || stripos($content, 'javascript:') !== false) {
                throw new RuntimeException('SVG faylı təhlükəsiz deyil: ' . $file->getClientOriginalName());
            }
        }

        $targetDir = public_path('uploads/' . $folder);
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $filename = now()->format('YmdHis') . '_' . Str::random(16) . '.' . $extension;
        $file->move($targetDir, $filename);

        return 'uploads/' . $folder . '/' . $filename;
    }

    private function scanFiles(): array
    {
        $uploadsRoot = public_path('uploads');
        $publicRoot = public_path();
        $items = [];

        if (! is_dir($uploadsRoot)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $relative = str_replace($publicRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relative = str_replace('\\', '/', $relative);

            if (! str_starts_with($relative, 'uploads/')) {
                continue;
            }

            $items[] = [
                'relative' => $relative,
                'name' => basename($relative),
                'folder' => dirname($relative),
                'ext' => strtolower(pathinfo($relative, PATHINFO_EXTENSION)),
                'size' => (int) $file->getSize(),
                'modified' => (int) $file->getMTime(),
                'is_image' => $this->isImage($relative),
                'url' => asset($relative),
            ];
        }

        usort($items, fn (array $a, array $b): int => $b['modified'] <=> $a['modified']);

        return $items;
    }

    private function filterFiles(array $files, Request $request): array
    {
        $q = Str::lower(trim((string) $request->query('q', '')));
        $folder = trim((string) $request->query('folder', ''));
        $type = trim((string) $request->query('type', ''));

        return array_values(array_filter($files, function (array $file) use ($q, $folder, $type): bool {
            if ($q !== '' && ! str_contains(Str::lower($file['relative'] . ' ' . $file['name']), $q)) {
                return false;
            }

            if ($folder !== '') {
                $folderFilter = 'uploads/' . $this->cleanFolder($folder);
                if (! str_starts_with($file['folder'], $folderFilter)) {
                    return false;
                }
            }

            if ($type === 'image' && ! $file['is_image']) {
                return false;
            }

            if ($type === 'document' && $file['is_image']) {
                return false;
            }

            return true;
        }));
    }

    private function safeRelativePath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');

        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'uploads/')) {
            throw new RuntimeException('Fayl yolu düzgün deyil.');
        }

        return $path;
    }

    private function cleanFolder(string $folder): string
    {
        $folder = trim($folder);
        $folder = trim($folder, "/\\");
        $folder = str_replace(['..', '\\'], ['', '/'], $folder);
        $folder = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $folder) ?: '';
        $folder = trim($folder, '/');

        return $folder !== '' ? $folder : 'media';
    }

    private function allowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'];
    }

    private function isImage(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
    }

}
