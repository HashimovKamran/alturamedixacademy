<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Services\Admin\AdminLogService;
use App\Services\Admin\UploadService;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    public function store(Request $request, UploadService $uploads, AdminLogService $logs): RedirectResponse
    {
        // Do not attach Laravel's generic `file` rule here. When PHP rejects an upload
        // before the controller runs, that rule only produces the opaque `validation.uploaded` key.
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_visual_type' => ['nullable', 'in:icon,image'],
        ], [
            'title.required' => 'Kateqoriya başlığı mütləqdir.',
        ]);

        $language = AdminLanguage::selected($request);
        $id = $request->integer('id');
        $category = $id > 0
            ? ArticleCategory::query()->findOrFail($id)
            : new ArticleCategory();

        $visualType = (string) $request->input('category_visual_type', '');
        if ($visualType === '') {
            $visualType = trim((string) $request->input('icon_class', '')) !== '' ? 'icon' : 'image';
        }

        $data = [
            'lang_code' => $language,
            'title' => trim((string) $request->input('title')),
            'slug' => Str::slug((string) $request->input('slug', '')) ?: Str::slug((string) $request->input('title')) ?: Str::random(8),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ];

        $requestedSort = $request->integer('sort_order');
        if ($category->exists) {
            $data['sort_order'] = $requestedSort > 0 ? $requestedSort : (int) $category->sort_order;
        } else {
            $data['sort_order'] = $requestedSort > 0
                ? $requestedSort
                : ((int) ArticleCategory::query()->where('lang_code', $language)->max('sort_order')) + 1;
        }

        if ($visualType === 'icon') {
            $iconClass = trim((string) $request->input('icon_class', ''));
            if ($iconClass === '') {
                return back()->withErrors([
                    'icon_class' => 'Kateqoriya üçün icon seçin və ya Şəkil rejiminə keçin.',
                ])->withInput();
            }

            $data['icon_class'] = $iconClass;
            $data['image_path'] = null;
        } else {
            $imagePath = trim((string) $category->image_path);
            /** @var UploadedFile|null $file */
            $file = $request->file('image_path');

            if ($file !== null) {
                $uploadError = $this->uploadErrorMessage($file);
                if ($uploadError !== null) {
                    return back()->withErrors(['image_path' => $uploadError])->withInput();
                }

                $extension = strtolower((string) $file->getClientOriginalExtension());
                $allowedExtensions = ['svg', 'png', 'jpg', 'jpeg', 'webp'];
                if (! in_array($extension, $allowedExtensions, true)) {
                    return back()->withErrors([
                        'image_path' => 'Kateqoriya şəkli SVG, PNG, JPG/JPEG və ya WEBP formatında olmalıdır.',
                    ])->withInput();
                }

                $uploadedPath = $uploads->store($file, 'categories');
                if ($uploadedPath === null) {
                    return back()->withErrors([
                        'image_path' => 'Kateqoriya şəkli serverə yazıla bilmədi. uploads/categories qovluğunun yazma icazəsini və PHP upload limitlərini yoxlayın.',
                    ])->withInput();
                }

                $imagePath = $uploadedPath;
            }

            if ($imagePath === '') {
                return back()->withErrors([
                    'image_path' => 'Kateqoriya üçün şəkil seçin. SVG, PNG, JPG/JPEG və WEBP formatları qəbul olunur.',
                ])->withInput();
            }

            $data['icon_class'] = null;
            $data['image_path'] = $imagePath;
        }

        $category->fill($data);
        $category->save();

        $logs->write(
            $request,
            'categories',
            $id > 0 ? 'update' : 'create',
            'Akademik kateqoriya yadda saxlanıldı: '.$category->title,
            ArticleCategory::class,
            (int) $category->id,
        );

        return redirect()
            ->route('admin.modules.index', ['module' => 'categories'])
            ->with('status', 'Kateqoriya yadda saxlanıldı.');
    }

    private function uploadErrorMessage(UploadedFile $file): ?string
    {
        if ($file->isValid()) {
            if ((int) $file->getSize() > self::MAX_IMAGE_BYTES) {
                return 'Kateqoriya şəkli maksimum 5 MB olmalıdır.';
            }

            return null;
        }

        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE => 'Şəkil PHP upload_max_filesize limitini keçir. Serverdə upload_max_filesize dəyərini ən azı 10M edin.',
            UPLOAD_ERR_FORM_SIZE => 'Şəkil formun icazə verdiyi ölçünü keçir. Maksimum 5 MB fayl seçin.',
            UPLOAD_ERR_PARTIAL => 'Şəkil yarımçıq yükləndi. Faylı yenidən seçib təkrar yoxlayın.',
            UPLOAD_ERR_NO_FILE => 'Kateqoriya üçün şəkil seçin.',
            UPLOAD_ERR_NO_TMP_DIR => 'Serverin müvəqqəti upload qovluğu tapılmadı. PHP upload_tmp_dir parametrini yoxlayın.',
            UPLOAD_ERR_CANT_WRITE => 'Server şəkli diskə yaza bilmədi. uploads/categories qovluğunun yazma icazəsini yoxlayın.',
            UPLOAD_ERR_EXTENSION => 'Server extension-u şəkil uploadunu dayandırdı. PHP extension konfiqurasiyasını yoxlayın.',
            default => 'Şəkil yüklənmədi. Serverin PHP upload limitlərini və müvəqqəti qovluq ayarlarını yoxlayın.',
        };
    }
}
