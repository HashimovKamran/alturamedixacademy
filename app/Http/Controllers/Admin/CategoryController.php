<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Services\Admin\AdminLogService;
use App\Services\Admin\UploadService;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function store(Request $request, UploadService $uploads, AdminLogService $logs): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_visual_type' => ['nullable', 'in:icon,image'],
            'image_path' => ['nullable', 'file', 'max:5120'],
        ], [
            'title.required' => 'Kateqoriya başlığı mütləqdir.',
            'image_path.max' => 'Kateqoriya şəkli maksimum 5 MB olmalıdır.',
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

            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');
                $extension = strtolower((string) $file?->getClientOriginalExtension());
                $allowedExtensions = ['svg', 'png', 'jpg', 'jpeg', 'webp'];

                if (! $file || ! $file->isValid() || ! in_array($extension, $allowedExtensions, true)) {
                    return back()->withErrors([
                        'image_path' => 'Kateqoriya şəkli SVG, PNG, JPG/JPEG və ya WEBP formatında olmalıdır.',
                    ])->withInput();
                }

                $uploadedPath = $uploads->store($file, 'categories');
                if ($uploadedPath === null) {
                    return back()->withErrors([
                        'image_path' => 'Kateqoriya şəkli yüklənmədi. SVG, PNG, JPG/JPEG və ya WEBP formatında, maksimum 5 MB olan etibarlı fayl seçin.',
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
}
