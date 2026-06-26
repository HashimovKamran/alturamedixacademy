<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Menu;
use App\Models\Page;
use App\Models\VisualBlock;
use App\Models\VisualEdit;
use App\Services\Admin\AdminLogService;
use App\Services\Site\VisualEditorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use App\Support\CleanUrl;

class VisualEditorController extends Controller
{
    public function index(Request $request, VisualEditorService $visual): View
    {
        $language = $this->selectedLanguage($request);
        $pages = $this->pages($language, $visual);
        $target = (string) $request->query('target', '/');

        if (! array_key_exists($target, $pages)) {
            $target = '/';
        }

        $pageKey = $this->pageKeyFromTarget($target, $visual);
        $previewUrl = CleanUrl::to($target) . (str_contains(CleanUrl::to($target), '?') ? '&' : '?') . http_build_query([
            'lang' => $language,
            've' => 1,
            'v' => time(),
        ]);

        return view('admin.visual_editor.index', [
            'languages' => Language::query()->active()->orderBy('sort_order')->orderBy('id')->get(),
            'selectedLanguage' => $language,
            'pages' => $pages,
            'target' => $target,
            'pageKey' => $pageKey,
            'previewUrl' => $previewUrl,
            'apiUrl' => route('admin.visual-editor.api'),
            'adminName' => optional(\App\Models\AdminUser::query()->find($request->session()->get('admin_user_id')))->full_name ?: 'Admin',
            'parentMenus' => Menu::query()
                ->where('lang_code', $language)
                ->where(function ($query): void {
                    $query->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'title']),
            'existingEdits' => VisualEdit::query()
                ->where('lang_code', $language)
                ->whereIn('page_key', ['_global', $pageKey])
                ->active()
                ->orderByRaw("CASE WHEN page_key = '_global' THEN 0 ELSE 1 END")
                ->orderBy('id')
                ->get(),
            'existingBlocks' => VisualBlock::query()
                ->where('lang_code', $language)
                ->whereIn('page_key', ['_global', $pageKey])
                ->active()
                ->orderByRaw("CASE WHEN page_key = '_global' THEN 0 ELSE 1 END")
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function createPage(Request $request, VisualEditorService $visual, AdminLogService $logs): RedirectResponse
    {
        $data = $request->validate([
            'lang_code' => ['required', 'string', 'max:10'],
            'title' => ['required', 'string', 'max:255'],
            'page_key' => ['nullable', 'string', 'max:120'],
            'menu_mode' => ['nullable', 'string', 'in:none,main,sub'],
            'parent_menu_id' => ['nullable', 'integer'],
        ]);

        $language = strtolower(trim($data['lang_code']));
        $title = trim($data['title']);
        $pageKey = $visual->cleanKey((string) ($data['page_key'] ?? ''));

        if ($pageKey === 'index') {
            $pageKey = $this->slug($title);
        }

        Page::query()->firstOrCreate(
            ['lang_code' => $language, 'page_key' => $pageKey],
            [
                'title' => $title,
                'subtitle' => '',
                'body' => '',
                'meta_description' => '',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        $menuMode = (string) ($data['menu_mode'] ?? 'main');
        if ($menuMode !== 'none') {
            Menu::query()->create([
                'lang_code' => $language,
                'parent_id' => $menuMode === 'sub' ? (int) ($data['parent_menu_id'] ?? 0) ?: null : null,
                'title' => $title,
                'url' => '/page?key=' . $pageKey,
                'target' => '_self',
                'sort_order' => ((int) Menu::query()->where('lang_code', $language)->max('sort_order')) + 1,
                'is_active' => true,
            ]);
        }

        $logs->write($request, 'live_editor', 'create_page', 'Yeni səhifə yaradıldı: ' . $title, 'page');

        return redirect()->route('admin.page-builder.index', [
            'lang_code' => $language,
            'page' => $pageKey,
        ])->with('status', 'Səhifə yaradıldı.');
    }

    public function api(Request $request, AdminLogService $logs, VisualEditorService $visual): JsonResponse
    {
        $action = (string) $request->input('action', '');
        $language = $this->selectedLanguage($request);
        $pageKey = $visual->cleanKey((string) $request->input('page_key', 'index'));

        try {
            return match ($action) {
                'save_batch' => $this->saveBatch($request, $logs, $language, $pageKey),
                'save_edit' => $this->saveEdit($request, $logs, $language, $pageKey),
                'save_block' => $this->saveBlock($request, $logs, $language, $pageKey),
                'reset_page' => $this->resetPage($request, $logs, $language, $pageKey),
                'upload_image' => $this->uploadImage($request),
                default => response()->json(['ok' => false, 'message' => 'Naməlum action: ' . $action], 422),
            };
        } catch (RuntimeException $exception) {
            return response()->json(['ok' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    private function saveBatch(Request $request, AdminLogService $logs, string $language, string $pageKey): JsonResponse
    {
        $edits = json_decode((string) $request->input('edits', '[]'), true);
        $blocks = json_decode((string) $request->input('blocks', '[]'), true);
        $savedEdits = 0;
        $savedBlocks = 0;

        foreach (is_array($edits) ? $edits : [] as $edit) {
            if (! is_array($edit)) {
                continue;
            }

            if ($this->upsertEdit($language, $pageKey, (string) ($edit['selector'] ?? ''), (string) ($edit['edit_type'] ?? ''), (string) ($edit['edit_value'] ?? ''))) {
                $savedEdits++;
            }
        }

        foreach (is_array($blocks) ? $blocks : [] as $block) {
            if (! is_array($block)) {
                continue;
            }

            if ($this->insertBlock($language, $pageKey, (string) ($block['target_selector'] ?? 'main'), (string) ($block['block_html'] ?? ''), (int) ($block['sort_order'] ?? time()))) {
                $savedBlocks++;
            }
        }

        $logs->write($request, 'visual_editor', 'save_batch', 'Visual dəyişikliklər yadda saxlanıldı: ' . $pageKey, 'page');

        return response()->json(['ok' => true, 'message' => 'Yadda saxlanıldı.', 'saved_edits' => $savedEdits, 'saved_blocks' => $savedBlocks]);
    }

    private function saveEdit(Request $request, AdminLogService $logs, string $language, string $pageKey): JsonResponse
    {
        $saved = $this->upsertEdit(
            $language,
            $pageKey,
            (string) $request->input('selector', ''),
            (string) $request->input('edit_type', ''),
            (string) $request->input('edit_value', ''),
        );

        if (! $saved) {
            throw new RuntimeException('Selector və edit tipi düzgün deyil.');
        }

        $logs->write($request, 'visual_editor', 'save_edit', 'Visual edit yadda saxlanıldı: ' . $pageKey, 'visual_edit');

        return response()->json(['ok' => true, 'message' => 'Edit yadda saxlanıldı.']);
    }

    private function saveBlock(Request $request, AdminLogService $logs, string $language, string $pageKey): JsonResponse
    {
        $saved = $this->insertBlock(
            $language,
            $pageKey,
            (string) $request->input('target_selector', 'main'),
            (string) $request->input('block_html', ''),
            (int) $request->input('sort_order', time()),
        );

        if (! $saved) {
            throw new RuntimeException('Blok HTML boş ola bilməz.');
        }

        $logs->write($request, 'visual_editor', 'save_block', 'Visual blok əlavə edildi: ' . $pageKey, 'visual_block');

        return response()->json(['ok' => true, 'message' => 'Blok yadda saxlanıldı.']);
    }

    private function resetPage(Request $request, AdminLogService $logs, string $language, string $pageKey): JsonResponse
    {
        VisualEdit::query()->where('lang_code', $language)->where('page_key', $pageKey)->update(['is_active' => false]);
        VisualBlock::query()->where('lang_code', $language)->where('page_key', $pageKey)->update(['is_active' => false]);

        $logs->write($request, 'visual_editor', 'reset_page', 'Visual səhifə sıfırlandı: ' . $pageKey, 'page');

        return response()->json(['ok' => true, 'message' => 'Səhifənin visual dəyişiklikləri sıfırlandı.']);
    }

    private function uploadImage(Request $request): JsonResponse
    {
        $file = $request->file('image');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw new RuntimeException('Şəkil göndərilməyib.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new RuntimeException('Şəkil maksimum 10MB ola bilər.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            throw new RuntimeException('Bu fayl tipi icazəli deyil.');
        }

        if ($extension === 'svg') {
            $content = file_get_contents($file->getRealPath());
            if ($content === false || stripos($content, '<script') !== false || stripos($content, 'onload=') !== false || stripos($content, 'javascript:') !== false) {
                throw new RuntimeException('SVG faylı təhlükəsiz deyil.');
            }
        }

        $dir = public_path('uploads/visual');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = 've_' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $extension;
        $file->move($dir, $filename);
        $path = 'uploads/visual/' . $filename;

        return response()->json(['ok' => true, 'message' => 'Şəkil yükləndi.', 'path' => $path, 'url' => asset($path)]);
    }

    private function upsertEdit(string $language, string $pageKey, string $selector, string $type, string $value): bool
    {
        $selector = mb_substr(trim($selector), 0, 360, 'UTF-8');
        $type = trim($type);

        if ($selector === '' || ! in_array($type, ['text', 'html', 'href', 'src', 'hide', 'style'], true)) {
            return false;
        }

        VisualEdit::query()->updateOrCreate(
            ['lang_code' => $language, 'page_key' => $pageKey, 'selector' => $selector, 'edit_type' => $type],
            ['edit_value' => $value, 'is_active' => true],
        );

        return true;
    }

    private function insertBlock(string $language, string $pageKey, string $targetSelector, string $html, int $sortOrder): bool
    {
        $targetSelector = mb_substr(trim($targetSelector) ?: 'main', 0, 360, 'UTF-8');
        $html = trim($html);

        if ($html === '') {
            return false;
        }

        VisualBlock::query()->create([
            'lang_code' => $language,
            'page_key' => $pageKey,
            'target_selector' => $targetSelector,
            'block_html' => $html,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        return true;
    }

    private function selectedLanguage(Request $request): string
    {
        $language = strtolower((string) $request->query('lang_code', $request->input('lang_code', 'az')));
        $validLanguages = Language::query()->active()->pluck('code')->all();

        return in_array($language, $validLanguages, true) ? $language : 'az';
    }

    private function pages(string $language, VisualEditorService $visual): array
    {
        $pages = [
            '/' => 'Ana səhifə',
            '/about' => 'Haqqımızda',
            '/articles' => 'Akademik yazılar',
            '/certificates' => 'Diplom və sertifikatlar',
            '/trainings' => 'Təlimlər',
            '/gallery' => 'Qalereya',
            '/contact' => 'Əlaqə',
        ];

        Page::query()
            ->where('lang_code', $language)
            ->orderBy('id')
            ->get(['page_key', 'title'])
            ->each(function (Page $page) use (&$pages, $visual): void {
                $key = $visual->cleanKey((string) $page->page_key);
                $title = trim((string) $page->title);

                if ($key !== '' && $title !== '' && ! in_array($key, ['index', 'about', 'articles', 'certificates', 'trainings', 'gallery', 'contact'], true)) {
                    $pages['/page?key=' . urlencode($key)] = $title;
                }
            });

        return $pages;
    }

    private function pageKeyFromTarget(string $target, VisualEditorService $visual): string
    {
        $parts = parse_url($target);
        if (! empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
            if (! empty($query['key'])) {
                return $visual->cleanKey((string) $query['key']);
            }
        }

        $path = (string) ($parts['path'] ?? $target);
        if ($path === '' || $path === '/') {
            return 'index';
        }

        $base = basename($path);
        return $visual->cleanKey((string) preg_replace('/\.php$/i', '', $base));
    }

    private function slug(string $text): string
    {
        $text = strtr(mb_strtolower(trim($text), 'UTF-8'), [
            'ə' => 'e', 'ö' => 'o', 'ü' => 'u', 'ı' => 'i', 'ğ' => 'g', 'ş' => 's', 'ç' => 'c',
        ]);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?: '';
        $text = trim($text, '-');

        return $text !== '' ? $text : 'sehife-' . time();
    }
}
