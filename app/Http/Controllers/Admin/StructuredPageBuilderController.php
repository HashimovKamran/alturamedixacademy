<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBuilderBlock;
use App\Models\PagePublication;
use App\Models\PageRevision;
use App\Services\Admin\AdminLogService;
use App\Services\Admin\UploadService;
use App\Services\Site\PagePublicationService;
use App\Support\Admin\AdminLanguage;
use App\Support\Cms\PageBlockRegistry;
use App\Support\Cms\SafeHtml;
use App\Support\Cms\SafeUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StructuredPageBuilderController extends Controller
{
    public function index(Request $request, PageBlockRegistry $registry): View
    {
        $language = $this->language($request);
        $pages = $this->pages($language);
        $pageKey = $this->key((string) $request->query('page', 'index'));
        if (! array_key_exists($pageKey, $pages)) {
            $pageKey = array_key_first($pages) ?: 'index';
        }

        $edit = $request->integer('edit') > 0
            ? PageBuilderBlock::query()->where('lang_code', $language)->find($request->integer('edit'))
            : null;
        if ($edit) {
            $pageKey = $this->key((string) $edit->page_key);
        }

        return view('admin.page_builder.structured', [
            'languages' => AdminLanguage::activeLanguages(),
            'selectedLanguage' => $language,
            'pages' => $pages,
            'pageKey' => $pageKey,
            'blocks' => PageBuilderBlock::query()->where('lang_code', $language)->where('page_key', $pageKey)->orderBy('sort_order')->orderBy('id')->get(),
            'edit' => $edit,
            'settings' => array_merge($registry->defaults(), $this->decode($edit?->settings_json)),
            'contentItems' => is_array($edit?->content_json) ? ($edit->content_json['items'] ?? []) : [],
            'blockTypes' => $registry->types(),
            'publication' => Schema::hasTable('aa_page_publications') ? PagePublication::query()->where('lang_code', $language)->where('page_key', $pageKey)->first() : null,
            'revisions' => Schema::hasTable('aa_page_revisions') ? PageRevision::query()->where('lang_code', $language)->where('page_key', $pageKey)->latest('version')->limit(10)->get() : collect(),
            'previewUrl' => $this->previewUrl($pageKey, $language),
        ]);
    }

    public function store(Request $request, UploadService $uploads, AdminLogService $logs, PageBlockRegistry $registry, SafeHtml $html): RedirectResponse
    {
        $request->validate([
            'id' => ['nullable', 'integer'], 'page_key' => ['required', 'string', 'max:120'],
            'block_type' => ['required', 'string'], 'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'], 'body' => ['nullable', 'string', 'max:100000'],
            'button_text' => ['nullable', 'string', 'max:255'], 'button_url' => ['nullable', 'string', 'max:700'],
            'image_path' => ['nullable', 'file', 'max:10240'], 'content_json' => ['nullable', 'string', 'max:100000'],
        ]);

        $language = $this->language($request);
        $pageKey = $this->key((string) $request->input('page_key'));
        $type = (string) $request->input('block_type');
        abort_unless(array_key_exists($type, $registry->types()), 422);
        $id = $request->integer('id');
        $block = $id > 0 ? PageBuilderBlock::query()->where('lang_code', $language)->findOrFail($id) : new PageBuilderBlock;
        $content = $registry->content($type, $request->input('body'), $request->input('content_json'), $html);
        $sort = max(0, $request->integer('sort_order')) ?: ((int) PageBuilderBlock::query()->where('lang_code', $language)->where('page_key', $pageKey)->max('sort_order') + 1);

        $block->fill([
            'lang_code' => $language, 'page_key' => $pageKey, 'block_type' => $type,
            'title' => trim((string) $request->input('title')), 'subtitle' => trim((string) $request->input('subtitle')),
            'body' => $content['html'] ?? trim((string) $request->input('body')), 'content_json' => $content,
            'image_path' => $uploads->store($request->file('image_path'), 'page_builder') ?? $block->image_path,
            'button_text' => trim((string) $request->input('button_text')), 'button_url' => SafeUrl::clean($request->input('button_url')),
            'settings_json' => json_encode($registry->settings($request->all()), JSON_UNESCAPED_UNICODE),
            'sort_order' => $sort, 'is_active' => $request->boolean('is_active'),
        ])->save();

        $logs->write($request, 'page_builder', $id > 0 ? 'update_draft' : 'create_draft', 'Blok draft olaraq saxlanıldı: '.($block->title ?: $type), 'page_builder_block', (int) $block->id);

        return redirect()->route('admin.page-builder.index', ['lang_code' => $language, 'page' => $pageKey])->with('status', 'Draft saxlanıldı. Dərc etmək üçün “Dərc et” düyməsini basın.');
    }

    public function publish(Request $request, PagePublicationService $service, AdminLogService $logs): RedirectResponse
    {
        $data = $request->validate(['page_key' => ['required', 'string', 'max:120'], 'change_note' => ['nullable', 'string', 'max:255']]);
        $language = $this->language($request);
        $pageKey = $this->key($data['page_key']);
        $publication = $service->publish($language, $pageKey, $request->session()->get('admin_user_id'), $data['change_note'] ?? null);
        $logs->write($request, 'page_builder', 'publish', 'Səhifə dərc edildi: '.$pageKey.' v'.$publication->version, 'page_publication', (int) $publication->id);

        return back()->with('status', 'Səhifə dərc edildi. Versiya: '.$publication->version);
    }

    public function restore(Request $request, PageRevision $revision, PagePublicationService $service, AdminLogService $logs): RedirectResponse
    {
        $language = $this->language($request);
        abort_unless($revision->lang_code === $language, 404);
        $publication = $service->restore($revision, $request->session()->get('admin_user_id'));
        $logs->write($request, 'page_builder', 'restore', 'Versiya bərpa edildi: '.$revision->page_key.' v'.$revision->version, 'page_publication', (int) $publication->id);

        return redirect()->route('admin.page-builder.index', ['lang_code' => $language, 'page' => $revision->page_key])->with('status', 'Versiya '.$revision->version.' bərpa edildi.');
    }

    public function sort(Request $request, AdminLogService $logs): Response
    {
        $language = $this->language($request);
        $pageKey = $this->key((string) $request->input('page_key'));
        $order = $request->input('order', []);
        $order = is_string($order) ? (json_decode($order, true) ?: []) : (array) $order;
        foreach (array_values(array_unique(array_map('intval', $order))) as $index => $id) {
            PageBuilderBlock::query()->whereKey($id)->where('lang_code', $language)->where('page_key', $pageKey)->update(['sort_order' => $index + 1]);
        }
        $logs->write($request, 'page_builder', 'sort_draft', 'Draft sırası dəyişdirildi: '.$pageKey, 'page_builder_page');

        return response(['ok' => true]);
    }

    public function duplicate(Request $request, AdminLogService $logs, PageBuilderBlock $block): RedirectResponse
    {
        abort_unless($block->lang_code === $this->language($request), 404);
        $copy = $block->replicate(['block_uuid']);
        $copy->block_uuid = (string) Str::uuid();
        $copy->title = trim((string) $block->title).' - kopya';
        $copy->sort_order = ((int) PageBuilderBlock::query()->where('lang_code', $block->lang_code)->where('page_key', $block->page_key)->max('sort_order')) + 1;
        $copy->save();
        $logs->write($request, 'page_builder', 'duplicate_draft', 'Blok kopyalandı: '.$block->title, 'page_builder_block', (int) $copy->id);

        return back()->with('status', 'Blok draft daxilində kopyalandı.');
    }

    public function destroy(Request $request, AdminLogService $logs, PageBuilderBlock $block): RedirectResponse
    {
        $language = $this->language($request);
        abort_unless($block->lang_code === $language, 404);
        $key = $block->page_key;
        $label = $block->title ?: $block->block_type;
        $id = (int) $block->id;
        $block->delete();
        $logs->write($request, 'page_builder', 'delete_draft', 'Blok draft-dan silindi: '.$label, 'page_builder_block', $id);

        return redirect()->route('admin.page-builder.index', ['lang_code' => $language, 'page' => $key])->with('status', 'Draft dəyişdi; public versiya dəyişməyib.');
    }

    private function pages(string $language): array
    {
        $pages = ['index' => 'Ana səhifə', 'about' => 'Haqqımızda', 'articles' => 'Akademik yazılar', 'certificates' => 'Sertifikatlar', 'trainings' => 'Təlimlər', 'gallery' => 'Qalereya', 'contact' => 'Əlaqə'];
        foreach (Page::query()->where('lang_code', $language)->get(['page_key', 'title']) as $page) {
            $pages[$this->key($page->page_key)] = $page->title;
        }
        foreach (Menu::query()->where('lang_code', $language)->active()->get(['title', 'url']) as $menu) {
            $key = $this->keyFromUrl((string) $menu->url);
            if ($key !== '') {
                $pages[$key] = $menu->title;
            }
        }

        return $pages;
    }

    private function keyFromUrl(string $url): string
    {
        if ($url === '' || $url === '#') {
            return '';
        } $parts = parse_url($url);
        parse_str((string) ($parts['query'] ?? ''), $query);

        return ! empty($query['key']) ? $this->key((string) $query['key']) : $this->key((string) preg_replace('/\.php$/i', '', basename((string) ($parts['path'] ?? $url))));
    }

    private function previewUrl(string $key, string $language): string
    {
        $map = ['index' => '/', 'about' => '/about', 'articles' => '/articles', 'certificates' => '/certificates', 'trainings' => '/trainings', 'gallery' => '/gallery', 'contact' => '/contact'];
        $path = $map[$key] ?? '/page?key='.urlencode($key);

        return url($path).(str_contains($path, '?') ? '&' : '?').http_build_query(['lang' => $language, 'pb_preview' => 1]);
    }

    private function language(Request $request): string
    {
        return AdminLanguage::selected($request);
    }

    private function key(string $key): string
    {
        return preg_replace('/[^a-z0-9_-]/', '', Str::lower(trim($key))) ?: 'index';
    }

    private function decode(?string $json): array
    {
        $data = json_decode((string) $json, true);

        return is_array($data) ? $data : [];
    }
}
