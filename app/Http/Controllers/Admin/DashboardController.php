<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ContactMessage;
use App\Models\GalleryItem;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Partner;
use App\Models\SiteUser;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $logs = AdminLog::query()->latest('created_at')->limit(12)->get();
        if ($logs->isEmpty()) {
            $logs = collect([
                ...$this->latestRows(Article::class, 'title', 'articles', 'article'),
                ...$this->latestRows(ArticleCategory::class, 'title', 'categories', 'category'),
                ...$this->latestRows(Slider::class, 'title', 'sliders', 'slider'),
                ...$this->latestRows(GalleryItem::class, 'title', 'gallery', 'gallery'),
                ...$this->latestRows(Page::class, 'title', 'pages', 'page'),
                ...$this->latestRows(Partner::class, 'title', 'partners', 'partner'),
            ])->sortByDesc('created_at')->take(12)->values();
        }

        return view('admin.dashboard', [
            'stats' => [
                ['title' => 'Sayt istifadəçiləri', 'value' => SiteUser::query()->count(), 'icon' => 'ti ti-users', 'url' => route('admin.users.index'), 'note' => 'Qeydiyyatdan keçənlər'],
                ['title' => 'Məqalələr', 'value' => Article::query()->count(), 'icon' => 'ti ti-news', 'url' => route('admin.modules.index', ['module' => 'articles']), 'note' => 'Akademik yazılar'],
                ['title' => 'Kateqoriyalar', 'value' => ArticleCategory::query()->count(), 'icon' => 'ti ti-category', 'url' => route('admin.modules.index', ['module' => 'categories']), 'note' => 'Akademik bölmələr'],
                ['title' => 'Sliderlər', 'value' => Slider::query()->count(), 'icon' => 'ti ti-photo', 'url' => route('admin.modules.index', ['module' => 'sliders']), 'note' => 'Ana səhifə sliderləri'],
                ['title' => 'Menyular', 'value' => Menu::query()->count(), 'icon' => 'ti ti-menu-2', 'url' => route('admin.modules.index', ['module' => 'menus']), 'note' => 'Header menyuları'],
                ['title' => 'Statik səhifələr', 'value' => Page::query()->count(), 'icon' => 'ti ti-file-text', 'url' => route('admin.modules.index', ['module' => 'pages']), 'note' => 'Haqqımızda / Əlaqə'],
                ['title' => 'Qalereya', 'value' => GalleryItem::query()->count(), 'icon' => 'ti ti-photo', 'url' => route('admin.modules.index', ['module' => 'gallery']), 'note' => 'Şəkil arxivi'],
                ['title' => 'Tərəfdaşlar', 'value' => Partner::query()->count(), 'icon' => 'ti ti-users-group', 'url' => route('admin.modules.index', ['module' => 'partners']), 'note' => 'Logo və linklər'],
                ['title' => 'Reklamlar', 'value' => Advertisement::query()->count(), 'icon' => 'ti ti-ad-2', 'url' => route('admin.modules.index', ['module' => 'ads']), 'note' => 'Sidebar / aşağı reklam'],
                ['title' => 'Oxunmamış mesajlar', 'value' => ContactMessage::query()->where('is_read', false)->count(), 'icon' => 'ti ti-mail-opened', 'url' => route('admin.contact.index'), 'note' => 'Əlaqə formu'],
            ],
            'activeStats' => [
                'activeArticles' => Article::query()->where('is_active', true)->count(),
                'inactiveArticles' => Article::query()->where('is_active', false)->count(),
                'activeSliders' => Slider::query()->where('is_active', true)->count(),
                'activeGallery' => GalleryItem::query()->where('is_active', true)->count(),
                'activeAds' => Advertisement::query()->where('is_active', true)->count(),
            ],
            'logs' => $logs,
        ]);
    }

    /**
     * @param class-string<Model> $model
     */
    private function latestRows(string $model, string $titleColumn, string $module, string $type): array
    {
        return $model::query()
            ->latest('updated_at')
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn ($row): array => [
                'module' => $module,
                'action' => 'update',
                'description' => (string) ($row->{$titleColumn} ?? ''),
                'object_type' => $type,
                'object_id' => $row->id,
                'created_at' => $row->updated_at ?? $row->created_at,
            ])
            ->all();
    }
}
