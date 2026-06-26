<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Certificate;
use App\Models\Menu;
use App\Services\Admin\AdminLogService;
use App\Services\Admin\ArticleNotificationService;
use App\Services\Admin\CertificateQrService;
use App\Services\Admin\UploadService;
use App\Support\Admin\AdminLanguage;
use App\Support\Admin\ContentModuleRegistry;
use App\Support\Cms\SafeHtml;
use App\Support\Cms\SafeUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContentModuleController extends Controller
{
    public function index(Request $request, CertificateQrService $certificateQr, string $module): View
    {
        $module = str_replace('-', '_', $module);
        $config = $this->config($module);
        $model = $config['model'];
        $language = $this->selectedLanguage($request);
        $edit = null;

        if ($request->integer('edit') > 0) {
            $edit = $model::query()->find($request->integer('edit'));
        }

        $query = $model::query();
        if ($this->hasLanguageField($config)) {
            $query->where('lang_code', $language);
        }

        $this->applyFilters($query, $model, $request);

        $order = $config['order'] ?? ['id', 'desc'];
        $rows = $query->orderBy($order[0], $order[1])->orderByDesc('id')->paginate(40)->withQueryString();
        $articleCategories = ArticleCategory::query()
            ->where('lang_code', $language)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'icon_class', 'image_path']);

        return view('admin.modules.index', [
            'moduleKey' => $module,
            'module' => $config,
            'rows' => $rows,
            'edit' => $edit,
            'certificateAspectRatio' => $module === 'certificates_manage' && $edit instanceof Certificate
                ? $certificateQr->previewAspectRatio($edit)
                : null,
            'languages' => AdminLanguage::activeLanguages(),
            'selectedLanguage' => $language,
            'categoryOptions' => $articleCategories->pluck('title', 'id')->all(),
            'categoryRecords' => $articleCategories
                ->mapWithKeys(fn (ArticleCategory $category): array => [
                    $category->id => [
                        'title' => (string) $category->title,
                        'icon_class' => (string) $category->icon_class,
                        'image_path' => (string) $category->image_path,
                    ],
                ])
                ->all(),
            'menuParentOptions' => Menu::query()
                ->where('lang_code', $language)
                ->where(function ($builder): void {
                    $builder->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->orderBy('sort_order')
                ->pluck('title', 'id')
                ->all(),
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'status' => (string) $request->query('status', ''),
                'active' => (string) $request->query('active', ''),
                'category_id' => (string) $request->query('category_id', ''),
            ],
        ]);
    }

    public function store(Request $request, UploadService $uploads, AdminLogService $logs, ArticleNotificationService $notifications, CertificateQrService $certificateQr, SafeHtml $html, string $module): RedirectResponse
    {
        $module = str_replace('-', '_', $module);
        $config = $this->config($module);
        $modelClass = $config['model'];
        $id = $request->integer('id');
        /** @var Model $model */
        $model = $id > 0 ? $modelClass::query()->findOrFail($id) : new $modelClass;
        $data = [];

        foreach ($config['fields'] as $name => $field) {
            $type = $field['type'];

            if ($type === 'language') {
                $data[$name] = $this->selectedLanguage($request);

                continue;
            }

            if ($type === 'checkbox') {
                $data[$name] = $request->boolean($name);

                continue;
            }

            if ($type === 'qr_position') {
                $data['qr_x'] = $this->clampFloat($request->input('qr_x', 72), 0, 100);
                $data['qr_y'] = $this->clampFloat($request->input('qr_y', 72), 0, 100);
                $data['qr_size'] = $this->clampFloat($request->input('qr_size', 16), 8, 35);

                continue;
            }

            if ($type === 'certificate_revocation') {
                $isRevoked = $request->boolean('is_revoked');
                $revokeReason = trim((string) $request->input('revoke_reason', ''));

                if ($isRevoked && $revokeReason === '') {
                    return back()
                        ->withErrors(['revoke_reason' => 'Sənəd ləğv edilirsə, səbəb mütləq qeyd olunmalıdır.'])
                        ->withInput();
                }

                $data['status'] = $isRevoked ? 'revoked' : 'valid';
                $data['revoke_reason'] = $isRevoked ? $revokeReason : null;

                continue;
            }

            if ($type === 'certificate_validity') {
                $validityPeriod = (string) $request->input('validity_period', '2_years');
                $issueDate = trim((string) $request->input('issue_date', ''));
                $baseDate = $issueDate !== '' ? Carbon::parse($issueDate) : today();

                if ($validityPeriod === 'lifetime') {
                    $data['expire_date'] = null;

                    continue;
                }

                if (in_array($validityPeriod, ['1_year', '2_years', '3_years'], true)) {
                    $years = match ($validityPeriod) {
                        '1_year' => 1,
                        '3_years' => 3,
                        default => 2,
                    };
                    $data['expire_date'] = $baseDate->copy()->addYearsNoOverflow($years)->toDateString();

                    continue;
                }

                if ($validityPeriod === 'custom') {
                    $expireDate = trim((string) $request->input('expire_date', ''));
                    if ($expireDate === '') {
                        return back()
                            ->withErrors(['expire_date' => 'Custom müddət üçün bitmə tarixini seçin.'])
                            ->withInput();
                    }

                    if ($issueDate !== '' && Carbon::parse($expireDate)->lt($baseDate)) {
                        return back()
                            ->withErrors(['expire_date' => 'Bitmə tarixi verilmə tarixindən əvvəl ola bilməz.'])
                            ->withInput();
                    }

                    $data['expire_date'] = Carbon::parse($expireDate)->toDateString();

                    continue;
                }

                return back()
                    ->withErrors(['validity_period' => 'Sənədin etibarlılıq müddətini seçin.'])
                    ->withInput();
            }

            if (in_array($type, ['file', 'svg_file'], true)) {
                $file = $request->file($name);
                if ($type === 'svg_file' && $file) {
                    $validationResponse = $this->validateSvgFile($file);
                    if ($validationResponse) {
                        return $validationResponse;
                    }
                }

                $path = $uploads->store($file, $config['folder']);
                if ($path !== null) {
                    $data[$name] = $path;
                } elseif (! $model->exists) {
                    $data[$name] = null;
                }

                continue;
            }

            $value = $request->input($name);

            if ($type === 'slug') {
                $source = $value ?: $request->input('title', '');
                $value = Str::slug((string) $source) ?: Str::random(8);
            }

            if (in_array($type, ['category', 'menu_parent'], true)) {
                $value = (int) $value > 0 ? (int) $value : null;
            }

            if ($name === 'cert_no' && trim((string) $value) === '') {
                $value = 'ALT-'.now()->year.'-'.strtoupper(Str::random(8));
            }

            if ($type === 'datetime' && trim((string) $value) === '') {
                $value = null;
            }

            if (in_array($name, ['body', 'content'], true) && in_array($module, ['pages', 'articles'], true)) {
                $value = $html->clean((string) $value);
            }

            if (str_ends_with($name, '_url') || $name === 'url') {
                $value = SafeUrl::clean((string) $value, '');
            }

            $data[$name] = is_string($value) ? trim($value) : $value;
        }

        if ($module === 'categories') {
            $validationResponse = $this->normalizeCategoryVisual($request, $model, $data);
            if ($validationResponse) {
                return $validationResponse;
            }
        }

        if (in_array($module, ['menus', 'sliders', 'ads', 'partners', 'categories', 'gallery', 'trainings'], true)) {
            $currentSort = (int) ($data['sort_order'] ?? 0);
            if ($id > 0) {
                $data['sort_order'] = $currentSort > 0 ? $currentSort : (int) $model->sort_order;
            } elseif ($currentSort <= 0) {
                $data['sort_order'] = ((int) $modelClass::query()
                    ->where('lang_code', $this->selectedLanguage($request))
                    ->max('sort_order')) + 1;
            }
        }

        $model->fill($data);
        $model->save();

        if ($module === 'certificates_manage' && $model instanceof Certificate) {
            $certificateQr->sync($model);
        }

        $label = $this->rowLabel($model);
        $logs->write(
            $request,
            $module,
            $id > 0 ? 'update' : 'create',
            $config['title'].' yadda saxlanıldı: '.$label,
            class_basename($model),
            (int) $model->getKey(),
        );

        $extraStatus = '';
        if ($module === 'articles' && $request->boolean('send_notify') && $model instanceof Article) {
            $result = $notifications->sendForArticle($model);
            $logs->write(
                $request,
                'articles',
                'notify',
                'Məqalə email bildirişi göndərildi: '.$label.'. Uğurlu: '.$result['sent'].', uğursuz: '.$result['failed'].', keçildi: '.$result['skipped'],
                'Article',
                (int) $model->id,
            );
            $extraStatus = ' Email: uğurlu '.$result['sent'].', uğursuz '.$result['failed'].', keçildi '.$result['skipped'].'.';
        }

        return redirect()
            ->route('admin.modules.index', ['module' => $module])
            ->with('status', 'Məlumat yadda saxlanıldı.'.$extraStatus);
    }

    public function destroy(Request $request, AdminLogService $logs, string $module, int $id): RedirectResponse
    {
        $module = str_replace('-', '_', $module);
        $config = $this->config($module);
        $model = $config['model']::query()->findOrFail($id);
        $label = $this->rowLabel($model);
        $model->delete();

        $logs->write(
            $request,
            $module,
            'delete',
            $config['title'].' silindi: '.$label,
            class_basename($model),
            $id,
        );

        return redirect()
            ->route('admin.modules.index', ['module' => $module])
            ->with('status', 'Məlumat silindi.');
    }

    public function sort(Request $request, AdminLogService $logs, string $module): JsonResponse
    {
        $module = str_replace('-', '_', $module);
        abort_unless(in_array($module, ['menus', 'sliders', 'ads', 'partners', 'categories', 'gallery', 'trainings'], true), 404);

        $config = $this->config($module);
        abort_unless(array_key_exists('sort_order', $config['fields']), 404);

        $modelClass = $config['model'];
        $language = $this->selectedLanguage($request);
        $order = $request->input('order', []);
        if (is_string($order)) {
            $order = json_decode($order, true) ?: [];
        }

        $ids = array_values(array_unique(array_map('intval', (array) $order)));
        if ($ids === []) {
            return response()->json(['ok' => false, 'message' => 'Sıralama boşdur.'], 422);
        }

        $validIds = $modelClass::query()
            ->where('lang_code', $language)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        $validLookup = array_flip($validIds);

        $sort = 1;
        foreach ($ids as $id) {
            if (! isset($validLookup[$id])) {
                continue;
            }

            $modelClass::query()
                ->whereKey($id)
                ->where('lang_code', $language)
                ->update(['sort_order' => $sort++]);
        }

        $logs->write($request, $module, 'sort', $config['title'].' sıralaması yeniləndi: '.strtoupper($language), class_basename($modelClass));

        return response()->json(['ok' => true]);
    }

    public function notifyArticle(Request $request, Article $article, ArticleNotificationService $notifications, AdminLogService $logs): RedirectResponse
    {
        $result = $notifications->sendForArticle($article);
        $logs->write(
            $request,
            'articles',
            'notify',
            'Məqalə email bildirişi göndərildi: '.$article->title.'. Uğurlu: '.$result['sent'].', uğursuz: '.$result['failed'].', keçildi: '.$result['skipped'],
            'Article',
            (int) $article->id,
        );

        return back()->with('status', 'Email bildirişi: uğurlu '.$result['sent'].', uğursuz '.$result['failed'].', keçildi '.$result['skipped'].'.');
    }

    private function applyFilters($query, string $modelClass, Request $request): void
    {
        $table = (new $modelClass)->getTable();
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $active = trim((string) $request->query('active', ''));
        $categoryId = (int) $request->query('category_id', 0);

        if ($q !== '') {
            $columns = array_values(array_filter(
                ['title', 'full_name', 'email', 'phone', 'cert_no', 'course_title', 'slug', 'url', 'subject', 'message', 'description', 'body'],
                fn (string $column): bool => Schema::hasColumn($table, $column),
            ));

            if ($columns) {
                $query->where(function ($builder) use ($columns, $q): void {
                    foreach ($columns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $builder->{$method}($column, 'like', '%'.$q.'%');
                    }
                });
            }
        }

        if ($status !== '' && Schema::hasColumn($table, 'status')) {
            if ($modelClass === Certificate::class) {
                if ($status === 'valid') {
                    $query->where('status', 'valid')
                        ->where(function ($builder): void {
                            $builder->whereNull('expire_date')->orWhereDate('expire_date', '>=', today());
                        });
                } elseif ($status === 'expired') {
                    $query->where(function ($builder): void {
                        $builder->where('status', 'expired')
                            ->orWhere(function ($expired): void {
                                $expired->where('status', '!=', 'revoked')->whereDate('expire_date', '<', today());
                            });
                    });
                } else {
                    $query->where('status', $status);
                }
            } else {
                $query->where('status', $status);
            }
        }

        if ($active !== '' && Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', $active === '1');
        }

        if ($categoryId > 0 && Schema::hasColumn($table, 'category_id')) {
            $query->where('category_id', $categoryId);
        }
    }

    private function config(string $module): array
    {
        $module = str_replace('-', '_', $module);
        abort_if($module === 'features', 404);
        $config = ContentModuleRegistry::get($module);

        abort_unless($config, 404);

        return $config;
    }

    private function validateSvgFile($file): ?RedirectResponse
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension !== 'svg') {
            return back()->withErrors(['image_path' => 'Kateqoriya şəkli yalnız SVG formatında olmalıdır.'])->withInput();
        }

        $content = file_get_contents($file->getRealPath());
        if ($content === false || ! app(UploadService::class)->safeSvg($content)) {
            return back()->withErrors(['image_path' => 'SVG faylı təhlükəsiz deyil.'])->withInput();
        }

        return null;
    }

    private function clampInt(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    private function clampFloat(mixed $value, float $min, float $max): float
    {
        $number = is_numeric($value) ? (float) $value : $min;

        return round(max($min, min($max, $number)), 3);
    }

    private function normalizeCategoryVisual(Request $request, Model $model, array &$data): ?RedirectResponse
    {
        $mode = (string) $request->input('category_visual_type', '');
        $iconClass = trim((string) ($data['icon_class'] ?? ''));
        $newImagePath = trim((string) ($data['image_path'] ?? ''));
        $currentImagePath = trim((string) ($model->image_path ?? ''));

        if ($mode === '') {
            $mode = $iconClass !== '' ? 'icon' : 'image';
        }

        if ($mode === 'icon') {
            if ($iconClass === '') {
                return back()->withErrors(['icon_class' => 'Kateqoriya üçün icon class seçilməlidir.'])->withInput();
            }

            $data['image_path'] = null;

            return null;
        }

        if ($mode === 'image') {
            $finalImagePath = $newImagePath !== '' ? $newImagePath : $currentImagePath;
            if ($finalImagePath === '') {
                return back()->withErrors(['image_path' => 'Kateqoriya üçün SVG şəkil seçilməlidir.'])->withInput();
            }

            $data['icon_class'] = null;

            return null;
        }

        return back()->withErrors(['icon_class' => 'Kateqoriya üçün icon və ya SVG şəkil seçin.'])->withInput();
    }

    private function selectedLanguage(Request $request): string
    {
        return AdminLanguage::selected($request);
    }

    private function hasLanguageField(array $config): bool
    {
        return array_key_exists('lang_code', $config['fields']);
    }

    private function rowLabel(Model $model): string
    {
        foreach (['title', 'full_name', 'cert_no', 'email', 'page_key', 'id'] as $column) {
            if (isset($model->{$column}) && trim((string) $model->{$column}) !== '') {
                return (string) $model->{$column};
            }
        }

        return '#'.$model->getKey();
    }
}
