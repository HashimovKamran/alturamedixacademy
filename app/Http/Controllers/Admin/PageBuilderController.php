<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBuilderBlock;
use App\Services\Admin\AdminLogService;
use App\Services\Admin\UploadService;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    public function index(Request $request): View
    {
        $language = $this->selectedLanguage($request);
        $pages = $this->pages($language);
        $pageKey = $this->cleanKey((string) $request->query('page', 'index'));
        if (! array_key_exists($pageKey, $pages)) {
            $pageKey = array_key_first($pages) ?: 'index';
        }

        $edit = null;
        if ($request->integer('edit') > 0) {
            $edit = PageBuilderBlock::query()->find($request->integer('edit'));
            if ($edit) {
                $language = $edit->lang_code;
                $pageKey = $this->cleanKey($edit->page_key);
            }
        }

        return view('admin.page_builder.index', [
            'languages' => AdminLanguage::activeLanguages(),
            'selectedLanguage' => $language,
            'pages' => $this->pages($language),
            'pageKey' => $pageKey,
            'blocks' => PageBuilderBlock::query()
                ->where('lang_code', $language)
                ->where('page_key', $pageKey)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'edit' => $edit,
            'settings' => array_merge($this->defaultSettings(), $edit ? $this->decodeSettings($edit->settings_json) : []),
            'blockTypes' => $this->blockTypes(),
        ]);
    }

    public function store(Request $request, UploadService $uploads, AdminLogService $logs): RedirectResponse
    {
        $id = $request->integer('id');
        $language = $this->selectedLanguage($request);
        $pageKey = $this->cleanKey((string) $request->input('page_key', 'index'));
        $blockType = (string) $request->input('block_type', 'text');
        if (! array_key_exists($blockType, $this->blockTypes())) {
            $blockType = 'text';
        }

        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return back()->withErrors(['title' => 'Blok başlığı boş ola bilməz.'])->withInput();
        }

        $block = $id > 0 ? PageBuilderBlock::query()->findOrFail($id) : new PageBuilderBlock();
        $imagePath = $block->image_path;
        $newImage = $uploads->store($request->file('image_path'), 'page_builder');
        if ($newImage !== null) {
            $imagePath = $newImage;
        }

        $sortOrder = (int) $request->input('sort_order', 0);
        if ($sortOrder <= 0) {
            $sortOrder = ((int) PageBuilderBlock::query()
                ->where('lang_code', $language)
                ->where('page_key', $pageKey)
                ->max('sort_order')) + 1;
        }

        $block->fill([
            'lang_code' => $language,
            'page_key' => $pageKey,
            'block_type' => $blockType,
            'title' => $title,
            'subtitle' => trim((string) $request->input('subtitle', '')),
            'body' => trim((string) $request->input('body', '')),
            'image_path' => $imagePath,
            'button_text' => trim((string) $request->input('button_text', '')),
            'button_url' => trim((string) $request->input('button_url', '#')),
            'settings_json' => json_encode($this->settingsFromRequest($request), JSON_UNESCAPED_UNICODE),
            'sort_order' => $sortOrder,
            'is_active' => $request->boolean('is_active'),
        ]);
        $block->save();

        $logs->write($request, 'pageedit', $id > 0 ? 'update' : 'create', 'Page editor bloku yadda saxlanıldı: ' . $title, 'page_builder_block', (int) $block->id);

        return redirect()
            ->route('admin.page-builder.index', ['page' => $pageKey])
            ->with('status', 'Blok yadda saxlanıldı.');
    }

    public function sort(Request $request, AdminLogService $logs): Response
    {
        $language = $this->selectedLanguage($request);
        $pageKey = $this->cleanKey((string) $request->input('page_key', 'index'));
        $order = $request->input('order', []);
        if (is_string($order)) {
            $order = json_decode($order, true) ?: [];
        }

        $i = 1;
        foreach ((array) $order as $id) {
            PageBuilderBlock::query()
                ->whereKey((int) $id)
                ->where('lang_code', $language)
                ->where('page_key', $pageKey)
                ->update(['sort_order' => $i++]);
        }

        $logs->write($request, 'pageedit', 'sort', 'Page editor blok sırası dəyişdirildi: ' . strtoupper($language) . ' / ' . $pageKey, 'page_builder_page');

        return response(['ok' => true]);
    }

    public function duplicate(Request $request, AdminLogService $logs, PageBuilderBlock $block): RedirectResponse
    {
        $copy = $block->replicate();
        $copy->title = trim((string) $block->title) . ' - kopya';
        $copy->sort_order = ((int) PageBuilderBlock::query()
            ->where('lang_code', $block->lang_code)
            ->where('page_key', $block->page_key)
            ->max('sort_order')) + 1;
        $copy->save();

        $logs->write($request, 'pageedit', 'duplicate', 'Page editor bloku kopyalandı: ' . $block->title, 'page_builder_block', (int) $copy->id);

        return redirect()
            ->route('admin.page-builder.index', ['page' => $block->page_key])
            ->with('status', 'Blok kopyalandı.');
    }

    public function destroy(Request $request, AdminLogService $logs, PageBuilderBlock $block): RedirectResponse
    {
        $language = $block->lang_code;
        $pageKey = $block->page_key;
        $title = $block->title ?: $block->block_type;
        $block->delete();

        $logs->write($request, 'pageedit', 'delete', 'Page editor bloku silindi: ' . $title, 'page_builder_block', (int) $block->id);

        return redirect()
            ->route('admin.page-builder.index', ['page' => $pageKey])
            ->with('status', 'Blok silindi.');
    }

    private function pages(string $language): array
    {
        $pages = [
            'index' => 'Ana səhifə',
            'about' => 'Haqqımızda',
            'articles' => 'Akademik yazılar',
            'certificates' => 'Diplom və sertifikatlar',
            'trainings' => 'Təlimlər',
            'gallery' => 'Qalereya',
            'contact' => 'Əlaqə',
        ];

        Page::query()
            ->where('lang_code', $language)
            ->orderBy('id')
            ->get(['page_key', 'title'])
            ->each(function (Page $page) use (&$pages): void {
                $key = $this->cleanKey($page->page_key);
                if ($key !== '' && trim((string) $page->title) !== '') {
                    $pages[$key] = $page->title;
                }
            });

        Menu::query()
            ->where('lang_code', $language)
            ->active()
            ->orderBy('sort_order')
            ->get(['title', 'url'])
            ->each(function (Menu $menu) use (&$pages): void {
                $key = $this->pageKeyFromUrl((string) $menu->url);
                if ($key !== '' && trim((string) $menu->title) !== '') {
                    $pages[$key] = $menu->title;
                }
            });

        return $pages;
    }

    private function pageKeyFromUrl(string $url): string
    {
        if ($url === '' || $url === '#') {
            return '';
        }

        $parts = parse_url($url);
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
            if (! empty($query['key'])) {
                return $this->cleanKey((string) $query['key']);
            }
        }

        $base = basename((string) ($parts['path'] ?? $url));
        return $this->cleanKey((string) preg_replace('/\.php$/i', '', $base));
    }

    private function selectedLanguage(Request $request): string
    {
        return AdminLanguage::selected($request);
    }

    private function cleanKey(string $key): string
    {
        $key = Str::lower(trim($key));
        $key = preg_replace('/[^a-z0-9_\-]/', '', $key) ?: '';

        return $key !== '' ? $key : 'index';
    }

    private function defaultSettings(): array
    {
        return [
            'bg_color' => '#ffffff',
            'text_color' => '#071728',
            'accent_color' => '#ff8a1c',
            'layout' => 'card',
            'align' => 'left',
            'radius' => '24',
            'shadow' => 'soft',
            'animation' => 'fade-up',
            'image_position' => 'right',
            'max_width' => 'full',
            'padding_y' => '48',
            'custom_class' => '',
        ];
    }

    private function settingsFromRequest(Request $request): array
    {
        $settings = $this->defaultSettings();
        foreach (array_keys($settings) as $key) {
            if ($request->has($key)) {
                $settings[$key] = trim((string) $request->input($key));
            }
        }

        $settings['radius'] = (string) max(0, min(60, (int) $settings['radius']));
        $settings['padding_y'] = (string) max(12, min(140, (int) $settings['padding_y']));

        return $settings;
    }

    private function decodeSettings(?string $json): array
    {
        $data = json_decode((string) $json, true);
        return is_array($data) ? $data : [];
    }

    private function blockTypes(): array
    {
        return [
            'hero' => 'Hero / böyük giriş',
            'text' => 'Sadə mətn',
            'image_text' => 'Şəkil + mətn',
            'cards' => 'Kartlar',
            'cta' => 'Çağırış bloku',
            'faq' => 'Sual-cavab',
        ];
    }

}
