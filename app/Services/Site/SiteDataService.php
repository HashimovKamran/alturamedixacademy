<?php

namespace App\Services\Site;

use App\AlturaPageBuilder\Services\PublicVisualPageResolver;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Block;
use App\Models\Certificate;
use App\Models\Feature;
use App\Models\GalleryItem;
use App\Models\HomeStat;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Partner;
use App\Models\SiteUser;
use App\Models\Slider;
use App\Models\Training;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SiteDataService
{
    public function __construct(
        private readonly LanguageService $languages,
        private readonly SettingService $settings,
        private readonly PublicVisualPageResolver $visualDocuments,
    ) {}

    public function language(Request $request): string
    {
        return $this->languages->currentCode($request);
    }

    public function shared(Request $request, string $language, string $activePage = ''): array
    {
        return [
            'lang' => $language,
            'activePage' => $activePage,
            'languages' => $this->languages->active(),
            'activeLanguage' => $this->languages->active()->firstWhere('code', $language),
            'settings' => $this->settings->all($language),
            'menus' => $this->menus($language),
            'currentUser' => $this->currentUser($request),
            'headerBuilderBlocks' => collect(),
            'footerBuilderBlocks' => collect(),
            'headerBuilderDocument' => $this->pageBuilderDocument($language, '__header', $this->builderPreview($request)),
            'footerBuilderDocument' => $this->pageBuilderDocument($language, '__footer', $this->builderPreview($request)),
        ];
    }

    public function setting(string $language, string $key, string $default = ''): string
    {
        return $this->settings->get($language, $key, $default);
    }

    public function menus(string $language): EloquentCollection
    {
        return Menu::query()
            ->with(['children' => fn ($query) => $query->where('lang_code', $language)->active()->orderBy('sort_order')->orderBy('id')])
            ->forLanguage($language)
            ->where(function ($query): void { $query->whereNull('parent_id')->orWhere('parent_id', 0); })
            ->active()->orderBy('sort_order')->orderBy('id')->get();
    }

    public function page(string $language, string $key): Page
    {
        return Page::query()->forLanguage($language)->where('page_key', $key)->active()->first()
            ?: new Page(['lang_code' => $language, 'page_key' => $key, 'title' => $this->fallbackPageTitle($language, $key), 'subtitle' => '', 'body' => '', 'image_path' => '', 'meta_description' => '', 'is_active' => true]);
    }

    public function pageBuilderBlocks(string $language, string $pageKey, bool $preview = false): Collection
    {
        return collect();
    }

    public function pageBuilderDocument(string $language, string $pageKey, bool $preview = false): array
    {
        return $this->visualDocuments->document($language, $pageKey, $preview);
    }

    public function builderPreview(Request $request): bool
    {
        return ($request->boolean('pb_preview') || $request->boolean('pb_editor')) && (int) $request->session()->get('admin_user_id', 0) > 0;
    }

    public function home(Request $request): array
    {
        $language = $this->language($request);
        return array_merge($this->shared($request, $language, 'index'), [
            'sliders' => $this->activeRows(Slider::class, $language, 'sort_order'),
            'stats' => $this->activeRows(HomeStat::class, $language, 'sort_order'),
            'categories' => ArticleCategory::query()->forLanguage($language)->active()->where('is_featured', true)->orderBy('sort_order')->orderBy('id')->limit(6)->get(),
            'articles' => Article::query()->with('category')->forLanguage($language)->active()->latest('published_at')->latest('id')->limit(24)->get(),
            'trainings' => Training::query()->forLanguage($language)->active()->orderBy('training_date')->orderBy('sort_order')->limit(24)->get(),
            'features' => $this->activeRows(Feature::class, $language, 'sort_order'),
            'partners' => Partner::query()->forLanguage($language)->active()->orderBy('sort_order')->orderBy('id')->limit(20)->get(),
            'sidebarAds' => Advertisement::query()->forLanguage($language)->active()->where('position_key', 'sidebar')->orderBy('sort_order')->orderBy('id')->get(),
            'bottomAds' => Advertisement::query()->forLanguage($language)->active()->where('position_key', 'bottom')->orderBy('sort_order')->orderBy('id')->get(),
            'blocks' => Block::query()->forLanguage($language)->active()->get()->keyBy('block_key'),
            'pageBuilderBlocks' => collect(),
            'pageBuilderDocument' => $this->pageBuilderDocument($language, 'index', $this->builderPreview($request)),
        ]);
    }

    public function articles(string $language): array
    {
        $categories = ArticleCategory::query()->with(['articles' => fn ($query) => $query->active()->forLanguage($language)->latest('published_at')->latest('id')])->forLanguage($language)->active()->orderBy('sort_order')->orderBy('id')->get();
        return ['categories' => $categories, 'articlesByCategory' => $categories->mapWithKeys(fn ($category) => [$category->slug => $category->articles])];
    }

    public function article(string $language, string $slug): ?Article
    {
        return Article::query()->with('category')->forLanguage($language)->where('slug', $slug)->active()->first();
    }

    public function gallery(string $language): EloquentCollection
    {
        return GalleryItem::query()->forLanguage($language)->active()->orderBy('sort_order')->orderByDesc('id')->get();
    }

    public function certificate(string $certificateNumber): ?Certificate
    {
        return Certificate::query()->active()->where('cert_no', strtoupper($certificateNumber))->first();
    }

    public function currentUser(Request $request): ?SiteUser
    {
        $userId = (int) $request->session()->get('site_user_id', 0);
        return $userId > 0 ? SiteUser::query()->active()->find($userId) : null;
    }

    private function activeRows(string $modelClass, string $language, string $orderColumn): EloquentCollection
    {
        return $modelClass::query()->forLanguage($language)->active()->orderBy($orderColumn)->orderBy('id')->get();
    }

    private function fallbackPageTitle(string $language, string $key): string
    {
        $titles = [
            'az' => ['about' => 'Haqqımızda', 'contact' => 'Əlaqə', 'certificates' => 'Diplom və sertifikatlar', 'gallery' => 'Qalereya', 'trainings' => 'Təlimlər', 'articles' => 'Akademik yazılar'],
            'en' => ['about' => 'About', 'contact' => 'Contact', 'certificates' => 'Diplomas and Certificates', 'gallery' => 'Gallery', 'trainings' => 'Courses', 'articles' => 'Academic Articles'],
            'ru' => ['about' => 'О нас', 'contact' => 'Контакты', 'certificates' => 'Дипломы и сертификаты', 'gallery' => 'Галерея', 'trainings' => 'Курсы', 'articles' => 'Научные статьи'],
            'tr' => ['about' => 'Hakkımızda', 'contact' => 'İletişim', 'certificates' => 'Diploma ve Sertifikalar', 'gallery' => 'Galeri', 'trainings' => 'Eğitimler', 'articles' => 'Akademik Yazılar'],
        ];

        return $titles[$language][$key] ?? ucfirst(str_replace(['-', '_'], ' ', $key));
    }
}
