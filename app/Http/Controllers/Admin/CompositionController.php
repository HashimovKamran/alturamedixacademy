<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockPattern;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBuilderBlock;
use App\Models\PagePublication;
use App\Models\PageRevision;
use App\PageBuilder\Registry\BlockDefinitionRegistry;
use App\PageBuilder\Rendering\Renderer as PageBuilderRenderer;
use App\PageBuilder\Services\PageDocumentService;
use App\Services\Admin\AdminLogService;
use App\Services\Admin\UploadService;
use App\Services\Site\BlockTreeService;
use App\Services\Site\PagePublicationService;
use App\Support\Admin\AdminLanguage;
use App\Support\Cms\SafeHtml;
use App\Support\Cms\SafeUrl;
use App\Support\Cms\StructuredBlockRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompositionController extends Controller
{
    public function visual(Request $request, PageDocumentService $documents, BlockDefinitionRegistry $definitions): View
    {
        $language=$this->language($request);
        $pages=$this->pages($language);
        $pageKey=$this->key((string)$request->query('page','index'));
        if(!array_key_exists($pageKey,$pages))$pageKey=array_key_first($pages)?:'index';

        return view('admin.page_builder.visual_v2',[
            'selectedLanguage'=>$language,'pages'=>$pages,'pageKey'=>$pageKey,
            'editorUrl'=>$this->editorUrl($pageKey,$language),
            'document'=>$documents->working($language,$pageKey),
            'schemas'=>$definitions->schemas('sections'),
            'insertableTypes'=>$definitions->all('sections'),
            'publication'=>PagePublication::query()->where('lang_code',$language)->where('page_key',$pageKey)->first(),
        ]);
    }

    public function index(Request $request, StructuredBlockRegistry $registry, BlockTreeService $tree): View
    {
        $language = $this->language($request);
        $pages = $this->pages($language);
        $pageKey = $this->key((string) $request->query('page', 'index'));
        if (! array_key_exists($pageKey, $pages)) $pageKey = array_key_first($pages) ?: 'index';

        $edit = $request->integer('edit') > 0
            ? PageBuilderBlock::query()->where('lang_code', $language)->find($request->integer('edit'))
            : null;
        if ($edit) $pageKey = $this->key((string) $edit->page_key);

        $blocks = PageBuilderBlock::query()->where('lang_code', $language)->where('page_key', $pageKey)
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.page_builder.composition', [
            'languages' => AdminLanguage::activeLanguages(),
            'selectedLanguage' => $language,
            'pages' => $pages,
            'pageKey' => $pageKey,
            'blocks' => $blocks,
            'blockTree' => $tree->flatten($blocks),
            'edit' => $edit,
            'settings' => array_merge($registry->defaults(), $this->decode($edit?->settings_json)),
            'content' => is_array($edit?->content_json) ? $edit->content_json : [],
            'blockTypes' => $registry->types(),
            'insertableTypes' => $registry->types(false),
            'schemas' => $registry->schemas(),
            'parentBlocks' => $blocks->filter(fn ($block) => $registry->definition($block->block_type)['slots'] !== []),
            'patterns' => BlockPattern::query()->active()->latest()->get(),
            'publication' => PagePublication::query()->where('lang_code', $language)->where('page_key', $pageKey)->first(),
            'revisions' => PageRevision::query()->where('lang_code', $language)->where('page_key', $pageKey)->latest('version')->limit(10)->get(),
            'previewUrl' => $this->previewUrl($pageKey, $language),
        ]);
    }

    public function store(Request $request, UploadService $uploads, AdminLogService $logs, StructuredBlockRegistry $registry, SafeHtml $html): RedirectResponse
    {
        $request->validate([
            'id' => ['nullable', 'integer'], 'page_key' => ['required', 'string', 'max:120'],
            'block_type' => ['required', 'string', 'max:80'], 'parent_block_uuid' => ['nullable', 'uuid'],
            'slot_key' => ['nullable', 'string', 'max:80'], 'region_key' => ['nullable', 'string', 'max:80'],
            'content' => ['nullable', 'array'], 'image_path' => ['nullable', 'file', 'max:10240'],
            'sort_order' => ['nullable', 'integer', 'min:0'], 'title' => ['nullable', 'string', 'max:255'],
        ]);

        $language = $this->language($request);
        $pageKey = $this->key((string) $request->input('page_key'));
        $id = $request->integer('id');
        $block = $id > 0 ? PageBuilderBlock::query()->where('lang_code', $language)->findOrFail($id) : new PageBuilderBlock;
        $systemBlock = $block->exists && $registry->isSystem((string) $block->block_type);
        $type = $systemBlock
            ? (string) $block->block_type
            : (string) $request->input('block_type');
        abort_unless($systemBlock || array_key_exists($type, $registry->types(false)), 422);
        if ($systemBlock) $pageKey = (string) $block->page_key;

        $parentUuid = $systemBlock ? null : (trim((string) $request->input('parent_block_uuid')) ?: null);
        $slot = $systemBlock ? 'default' : (preg_replace('/[^a-z0-9_-]/', '', Str::lower((string) $request->input('slot_key', 'default'))) ?: 'default');
        if ($parentUuid) {
            $parent = PageBuilderBlock::query()->where('lang_code', $language)->where('page_key', $pageKey)->where('block_uuid', $parentUuid)->firstOrFail();
            abort_if($block->exists && $this->isDescendant($parent, (string) $block->block_uuid), 422, 'Blok öz alt blokuna yerləşdirilə bilməz.');
            abort_unless($registry->canParent((string) $parent->block_type, $type, $slot), 422, 'Seçilən parent və slot bu blok tipini qəbul etmir.');
        }

        $inputContent = (array) $request->input('content', []);
        $content = $registry->normalizeContent($type, $inputContent, $html);
        $legacyTitle = trim((string) ($content['title'] ?? $request->input('title', '')));
        $legacyBody = (string) ($content['html'] ?? $content['text'] ?? '');
        $sort = max(0, $request->integer('sort_order')) ?: ((int) PageBuilderBlock::query()
            ->where('lang_code', $language)->where('page_key', $pageKey)->where('parent_block_uuid', $parentUuid)->max('sort_order') + 1);

        $block->fill([
            'lang_code' => $language, 'page_key' => $pageKey,
            'region_key' => $systemBlock ? (string) $block->region_key : (preg_replace('/[^a-z0-9_-]/', '', Str::lower((string) $request->input('region_key', 'main'))) ?: 'main'),
            'block_type' => $type, 'schema_version' => StructuredBlockRegistry::SCHEMA_VERSION,
            'parent_block_uuid' => $parentUuid, 'slot_key' => $slot,
            'title' => $legacyTitle, 'subtitle' => trim((string) ($content['eyebrow'] ?? '')),
            'body' => $legacyBody, 'content_json' => $content,
            'image_path' => $uploads->store($request->file('image_path'), 'page_builder') ?? $block->image_path,
            'button_text' => trim((string) ($content['button_text'] ?? '')),
            'button_url' => SafeUrl::clean($content['button_url'] ?? null),
            'settings_json' => json_encode($registry->settings($request->all()), JSON_UNESCAPED_UNICODE),
            'sort_order' => $sort, 'is_active' => $request->boolean('is_active'),
        ])->save();

        $logs->write($request, 'page_builder', $id > 0 ? 'update_draft' : 'create_draft', 'Blok draft olaraq saxlanıldı: '.($legacyTitle ?: $type), 'page_builder_block', (int) $block->id);
        return redirect()->route('admin.page-builder.index', ['lang_code' => $language, 'page' => $pageKey])->with('status', 'Draft saxlanıldı.');
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
        abort_unless($revision->lang_code === $this->language($request), 404);
        $publication = $service->restore($revision, $request->session()->get('admin_user_id'));
        $logs->write($request, 'page_builder', 'restore', 'Versiya bərpa edildi: '.$revision->page_key.' v'.$revision->version, 'page_publication', (int) $publication->id);
        return redirect()->route('admin.page-builder.index', ['lang_code' => $revision->lang_code, 'page' => $revision->page_key])->with('status', 'Versiya '.$revision->version.' bərpa edildi.');
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
        DB::transaction(function () use ($block): void {
            $source = $this->subtree($block);
            $map = $source->mapWithKeys(fn ($item) => [$item->block_uuid => (string) Str::uuid()]);
            $base = (int) PageBuilderBlock::query()->where('lang_code', $block->lang_code)->where('page_key', $block->page_key)->whereNull('parent_block_uuid')->max('sort_order') + 1;
            foreach ($source as $item) {
                $copy = $item->replicate(['block_uuid']);
                $copy->block_uuid = $map[$item->block_uuid];
                $copy->parent_block_uuid = $item->parent_block_uuid && $map->has($item->parent_block_uuid) ? $map[$item->parent_block_uuid] : $item->parent_block_uuid;
                if ($item->id === $block->id) $copy->sort_order = $base;
                $copy->save();
            }
        });
        $logs->write($request, 'page_builder', 'duplicate_draft', 'Blok ağacı kopyalandı: '.$block->block_type, 'page_builder_block', (int) $block->id);
        return back()->with('status', 'Blok və alt blokları kopyalandı.');
    }

    public function destroy(Request $request, AdminLogService $logs, StructuredBlockRegistry $registry, PageBuilderBlock $block): RedirectResponse
    {
        abort_unless($block->lang_code === $this->language($request), 404);
        abort_if($registry->isSystem((string) $block->block_type), 422, 'Sistem bloku silinmir; lazım olduqda passiv edin.');
        $key = $block->page_key;
        $ids = $this->subtree($block)->pluck('id');
        PageBuilderBlock::query()->whereIn('id', $ids)->delete();
        $logs->write($request, 'page_builder', 'delete_draft', 'Blok ağacı silindi: '.$block->block_type, 'page_builder_block', (int) $block->id);
        return redirect()->route('admin.page-builder.index', ['lang_code' => $block->lang_code, 'page' => $key])->with('status', 'Blok və alt blokları draft-dan silindi.');
    }

    public function savePattern(Request $request, PageBuilderBlock $block): RedirectResponse
    {
        abort_unless($block->lang_code === $this->language($request), 404);
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'category' => ['nullable', 'string', 'max:120']]);
        $rows = $this->subtree($block)->map(fn ($item) => collect($item->getAttributes())->except(['id', 'lang_code', 'page_key', 'created_at', 'updated_at'])->all())->values()->all();
        BlockPattern::query()->create([
            'name' => trim($data['name']), 'category' => trim((string) ($data['category'] ?? 'general')) ?: 'general',
            'root_type' => $block->block_type, 'blocks_json' => $rows,
            'created_by' => $request->session()->get('admin_user_id'), 'is_active' => true,
        ]);
        return back()->with('status', 'Blok kombinasiyası pattern kimi saxlanıldı.');
    }

    public function insertPattern(Request $request, BlockPattern $pattern): RedirectResponse
    {
        $data = $request->validate(['page_key' => ['required', 'string', 'max:120']]);
        $language = $this->language($request);
        $pageKey = $this->key($data['page_key']);
        $rows = collect($pattern->blocks_json ?? []);
        $map = $rows->mapWithKeys(fn ($row) => [(string) ($row['block_uuid'] ?? Str::uuid()) => (string) Str::uuid()]);
        $base = (int) PageBuilderBlock::query()->where('lang_code', $language)->where('page_key', $pageKey)->whereNull('parent_block_uuid')->max('sort_order') + 1;
        DB::transaction(function () use ($rows, $map, $language, $pageKey, $pattern, $base): void {
            foreach ($rows as $index => $row) {
                $oldUuid = (string) ($row['block_uuid'] ?? '');
                $parent = (string) ($row['parent_block_uuid'] ?? '');
                $row['block_uuid'] = $map[$oldUuid] ?? (string) Str::uuid();
                $row['parent_block_uuid'] = $parent !== '' && $map->has($parent) ? $map[$parent] : null;
                $row['lang_code'] = $language; $row['page_key'] = $pageKey;
                $row['pattern_source_id'] = $pattern->id;
                if ($index === 0) $row['sort_order'] = $base;
                PageBuilderBlock::query()->create($row);
            }
        });
        return redirect()->route('admin.page-builder.index', ['lang_code' => $language, 'page' => $pageKey])->with('status', 'Pattern səhifəyə əlavə edildi.');
    }

    public function inlineUpdate(Request $request, StructuredBlockRegistry $registry, SafeHtml $html, PageDocumentService $documents, BlockDefinitionRegistry $definitions): Response
    {
        $data=$request->validate(['block_uuid'=>['required','uuid'],'field'=>['required','string','max:120'],'value'=>['nullable','string','max:100000'],'page_key'=>['nullable','string','max:120'],'document_mode'=>['nullable','boolean']]);
        $language=$this->language($request);
        $pageKey=$this->key((string)($data['page_key']??''));
        if(($data['page_key']??null)&&($request->boolean('document_mode')||$documents->hasWorkingDocument($language,$pageKey))){
            $node=$documents->findWorkingNode($language,$pageKey,$data['block_uuid']);
            abort_unless($node,404);
            $type=(string)($node['type']??'rich_text');
            $field=collect($definitions->definition($type,'blocks')['fields']??[])->firstWhere('key',$data['field']);
            abort_unless($field && in_array($field['type'],['text','textarea','richtext','number'],true),422);
            $content=is_array($node['content']??null)?$node['content']:[];
            $content[$data['field']]=$data['value']??'';
            $normalized=$registry->normalizeContent($type,$content,$html);
            $documents->updateNode($language,$pageKey,$data['block_uuid'],function(array &$node) use ($normalized): void {
                $this->applyNodeContent($node,$normalized);
            },$request->session()->get('admin_user_id'));
            return response(['ok'=>true]);
        }
        $block=PageBuilderBlock::query()->where('block_uuid',$data['block_uuid'])->firstOrFail();
        $field=collect($registry->definition((string)$block->block_type)['fields'])->firstWhere('key',$data['field']);
        abort_unless($field && in_array($field['type'],['text','textarea','richtext','number'],true),422);
        $content=is_array($block->content_json)?$block->content_json:[];
        $content[$data['field']]=$data['value']??'';
        $block->update(['content_json'=>$registry->normalizeContent((string)$block->block_type,$content,$html)]);
        return response(['ok'=>true]);
    }

    public function entityInlineUpdate(Request $request, SafeHtml $html): Response
    {
        $data=$request->validate([
            'entity'=>['required','string','max:40'], 'entity_id'=>['required','string','max:120'],
            'field'=>['required','string','max:80'], 'value'=>['nullable','string','max:100000'],
            'lang'=>['required','string','max:5'], 'richtext'=>['nullable','boolean'],
        ]);
        $allowed=[
            'slider'=>[\App\Models\Slider::class,['title','subtitle','description','button_1_text','button_2_text']],
            'stat'=>[\App\Models\HomeStat::class,['number_text','title']],
            'category'=>[\App\Models\ArticleCategory::class,['title']],
            'article'=>[\App\Models\Article::class,['title','excerpt','body']],
            'training'=>[\App\Models\Training::class,['title','location']],
            'feature'=>[\App\Models\Feature::class,['title','description']],
            'partner'=>[\App\Models\Partner::class,['title']],
            'gallery'=>[\App\Models\GalleryItem::class,['title','description']],
            'advertisement'=>[\App\Models\Advertisement::class,['title']],
            'menu'=>[\App\Models\Menu::class,['title']],
            'block'=>[\App\Models\Block::class,['title','body','button_text']],
        ];
        if($data['entity']==='setting'){
            $keys=['site_name','site_slogan','footer_about','footer_links','footer_contact','footer_newsletter','newsletter_text','contact_phone','contact_email','contact_address','home_map_title','home_map_subtitle'];
            abort_unless(in_array($data['entity_id'],$keys,true)&&$data['field']==='setting_value',422);
            \App\Models\Setting::query()->updateOrCreate(
                ['lang_code'=>$data['lang'],'setting_key'=>$data['entity_id']],
                ['setting_value'=>mb_substr(trim(strip_tags((string)($data['value']??''))),0,5000),'is_active'=>true]
            );
            return response(['ok'=>true]);
        }
        abort_unless(isset($allowed[$data['entity']])&&ctype_digit($data['entity_id']),422);
        [$model,$fields]=$allowed[$data['entity']];
        abort_unless(in_array($data['field'],$fields,true),422);
        $record=$model::query()->whereKey((int)$data['entity_id'])->where('lang_code',$data['lang'])->firstOrFail();
        $value=(string)($data['value']??'');
        $value=$request->boolean('richtext')&&$data['field']==='body'?$html->clean($value):trim(strip_tags($value));
        $record->update([$data['field']=>mb_substr($value,0,$data['field']==='body'?100000:5000)]);
        return response(['ok'=>true]);
    }

    public function editorSettings(Request $request, StructuredBlockRegistry $registry, SafeHtml $html, UploadService $uploads, PageDocumentService $documents, BlockDefinitionRegistry $definitions): Response
    {
        $request->validate(['block_uuid'=>['required','uuid'],'page_key'=>['nullable','string','max:120'],'document_mode'=>['nullable','boolean'],'content'=>['nullable','array'],'image_path'=>['nullable','file','max:10240']]);
        $language=$this->language($request);
        $pageKey=$this->key((string)$request->input('page_key',''));
        if($request->filled('page_key')&&($request->boolean('document_mode')||$documents->hasWorkingDocument($language,$pageKey))){
            $uuid=(string)$request->input('block_uuid');
            $node=$documents->findWorkingNode($language,$pageKey,$uuid);
            abort_unless($node,404);
            $type=(string)($node['type']??'rich_text');
            $definition=$definitions->definition($type,'blocks');
            $current=is_array($node['content']??null)?$node['content']:[];
            $incoming=(array)$request->input('content',[]);
            foreach($definition['fields']??[] as $field){
                if(($field['type']??'')==='checkbox')$incoming[$field['key']]=$request->boolean('content.'.$field['key']);
            }
            $normalized=$registry->normalizeContent($type,array_merge($current,$incoming),$html);
            $settings=$registry->settings($request->all());
            $imagePath=$uploads->store($request->file('image_path'),'page_builder');
            $documents->updateNode($language,$pageKey,$uuid,function(array &$node) use ($normalized,$settings,$imagePath): void {
                $this->applyNodeContent($node,$normalized);
                $node['settings']=$settings;
                if($imagePath)$node['image_path']=$imagePath;
            },$request->session()->get('admin_user_id'));
            return response(['ok'=>true]);
        }
        $block=PageBuilderBlock::query()->where('block_uuid',$request->input('block_uuid'))->firstOrFail();
        $definition=$registry->definition((string)$block->block_type);
        $current=is_array($block->content_json)?$block->content_json:[];
        $incoming=(array)$request->input('content',[]);
        foreach($definition['fields'] as $field){
            if($field['type']==='checkbox')$incoming[$field['key']]=$request->boolean('content.'.$field['key']);
        }
        $block->update([
            'content_json'=>$registry->normalizeContent((string)$block->block_type,array_merge($current,$incoming),$html),
            'settings_json'=>json_encode($registry->settings($request->all()),JSON_UNESCAPED_UNICODE),
            'image_path'=>$uploads->store($request->file('image_path'),'page_builder')??$block->image_path,
        ]);
        return response(['ok'=>true]);
    }

    public function document(Request $request, PageDocumentService $documents): Response
    {
        $language = $this->language($request);
        $pageKey = $this->key((string) $request->query('page_key', $request->input('page_key', 'index')));

        return response([
            'ok' => true,
            'document' => $documents->working($language, $pageKey),
        ]);
    }

    public function saveDocument(Request $request, PageDocumentService $documents, AdminLogService $logs): Response
    {
        $data = $request->validate([
            'page_key' => ['required', 'string', 'max:120'],
            'document' => ['required'],
        ]);
        $language = $this->language($request);
        $pageKey = $this->key($data['page_key']);
        $document = is_string($data['document']) ? json_decode($data['document'], true) : $data['document'];
        abort_unless(is_array($document), 422, 'Document JSON düzgün deyil.');
        $documents->save($language, $pageKey, $document, $request->session()->get('admin_user_id'));
        $logs->write($request, 'page_builder', 'save_document_v2', 'V2 document draft saxlanıldı: '.$pageKey, 'page_builder_document');

        return response(['ok' => true, 'document' => $documents->working($language, $pageKey)]);
    }

    public function renderSection(Request $request, PageBuilderRenderer $renderer, PageDocumentService $documents): Response
    {
        $data = $request->validate([
            'page_key' => ['required', 'string', 'max:120'],
            'section_id' => ['required', 'string', 'max:120'],
            'section' => ['required', 'array'],
        ]);
        $language = $this->language($request);
        $pageKey = $this->key($data['page_key']);
        $document = $documents->ensureDocument([
            'sections' => [$data['section_id'] => $data['section']],
            'order' => [$data['section_id']],
        ], $language, $pageKey);

        return response(['ok' => true, 'html' => $renderer->renderDocument($document, [])]);
    }

    public function editorAction(Request $request, StructuredBlockRegistry $registry, PageDocumentService $documents, BlockDefinitionRegistry $definitions): Response
    {
        $data=$request->validate([
            'action'=>['required','in:add,delete,duplicate,up,down'],
            'block_uuid'=>['nullable','uuid'],'after_uuid'=>['nullable','uuid'],
            'page_key'=>['nullable','string','max:120'],'block_type'=>['nullable','string','max:80'],
            'document_mode'=>['nullable','boolean'],
        ]);
        $language=$this->language($request);
        $pageKey=$this->key((string)($data['page_key']??'index'));
        $adminId=$request->session()->get('admin_user_id');
        $useDocument=$request->boolean('document_mode')||$documents->hasWorkingDocument($language,$pageKey);
        if($useDocument){
            if($data['action']==='add'){
                $type=(string)($data['block_type']??'rich_text');
                $available=$definitions->all('sections');
                abort_unless(isset($available[$type]),422);
                $definition=$available[$type];
                abort_unless(!($definition['system']??false),422);
                $result=$documents->addSection($language,$pageKey,$type,$data['after_uuid']??null,$adminId);
                return response(['ok'=>true,'block_uuid'=>$result['node']['block_uuid'],'document'=>$result['document']]);
            }
            $uuid=(string)($data['block_uuid']??'');
            $node=$documents->findWorkingNode($language,$pageKey,$uuid);
            abort_unless($node,404);
            $definition=$definitions->definition((string)($node['type']??'rich_text'),'sections');
            if($data['action']==='delete'){
                abort_if($definition['system']??false,422);
                $documents->deleteNode($language,$pageKey,$uuid,$adminId);
            }elseif($data['action']==='duplicate'){
                abort_if($definition['system']??false,422);
                $documents->duplicateNode($language,$pageKey,$uuid,$adminId);
            }else{
                $documents->moveNode($language,$pageKey,$uuid,$data['action'],$adminId);
            }
            return response(['ok'=>true,'document'=>$documents->working($language,$pageKey)]);
        }
        if($data['action']==='add'){
            $type=(string)($data['block_type']??'rich_text');
            abort_unless(array_key_exists($type,$registry->types(false)),422);
            $after=!empty($data['after_uuid'])?PageBuilderBlock::query()->where('block_uuid',$data['after_uuid'])->first():null;
            $sort=$after?((int)$after->sort_order+1):1;
            PageBuilderBlock::query()->where('lang_code',$language)->where('page_key',$pageKey)->whereNull('parent_block_uuid')->where('sort_order','>=',$sort)->increment('sort_order');
            $defaults=collect($registry->definition($type)['fields'])->mapWithKeys(fn($field)=>[$field['key']=>$field['default']??''])->all();
            $block=PageBuilderBlock::query()->create([
                'lang_code'=>$language,'page_key'=>$pageKey,'region_key'=>'main','block_type'=>$type,
                'schema_version'=>StructuredBlockRegistry::SCHEMA_VERSION,'slot_key'=>'default','content_json'=>$defaults,
                'settings_json'=>json_encode($registry->defaults(),JSON_UNESCAPED_UNICODE),'sort_order'=>$sort,'is_active'=>true,
            ]);
            return response(['ok'=>true,'block_uuid'=>$block->block_uuid]);
        }
        $block=PageBuilderBlock::query()->where('block_uuid',$data['block_uuid']??'')->firstOrFail();
        if($data['action']==='delete'){
            abort_if($registry->isSystem((string)$block->block_type),422);
            PageBuilderBlock::query()->where('parent_block_uuid',$block->block_uuid)->update(['parent_block_uuid'=>$block->parent_block_uuid]);
            $block->delete();
        }elseif($data['action']==='duplicate'){
            $copy=$block->replicate(['block_uuid']);$copy->block_uuid=(string)Str::uuid();$copy->sort_order=(int)$block->sort_order+1;$copy->save();
        }else{
            $operator=$data['action']==='up'?'<':'>';
            $order=$data['action']==='up'?'desc':'asc';
            $other=PageBuilderBlock::query()->where('lang_code',$block->lang_code)->where('page_key',$block->page_key)
                ->where('parent_block_uuid',$block->parent_block_uuid)->where('sort_order',$operator,$block->sort_order)->orderBy('sort_order',$order)->first();
            if($other){$old=$block->sort_order;$block->update(['sort_order'=>$other->sort_order]);$other->update(['sort_order'=>$old]);}
        }
        return response(['ok'=>true]);
    }

    private function subtree(PageBuilderBlock $root)
    {
        $all = PageBuilderBlock::query()->where('lang_code', $root->lang_code)->where('page_key', $root->page_key)->get();
        $selected = collect([$root]);
        $parents = collect([$root->block_uuid]);
        while ($parents->isNotEmpty()) {
            $children = $all->whereIn('parent_block_uuid', $parents)->reject(fn ($item) => $selected->contains('id', $item->id));
            if ($children->isEmpty()) break;
            $selected = $selected->concat($children);
            $parents = $children->pluck('block_uuid');
        }
        return $selected;
    }

    private function isDescendant(PageBuilderBlock $candidate, string $uuid): bool
    {
        $seen = [];
        while ($candidate && $candidate->parent_block_uuid) {
            if ($candidate->block_uuid === $uuid || in_array($candidate->block_uuid, $seen, true)) return true;
            $seen[] = $candidate->block_uuid;
            $candidate = PageBuilderBlock::query()->where('block_uuid', $candidate->parent_block_uuid)->first();
        }
        return $candidate?->block_uuid === $uuid;
    }

    private function pages(string $language): array
    {
        $pages = ['__header' => 'Header', 'index' => 'Ana səhifə', 'about' => 'Haqqımızda', 'articles' => 'Akademik yazılar', 'article_detail' => 'Məqalə detalı', 'certificates' => 'Sertifikatlar', 'trainings' => 'Təlimlər', 'gallery' => 'Qalereya', 'contact' => 'Əlaqə', 'profile' => 'Profil', '__footer' => 'Footer'];
        foreach (Page::query()->where('lang_code', $language)->get(['page_key', 'title']) as $page) $pages[$this->key($page->page_key)] = $page->title;
        foreach (Menu::query()->where('lang_code', $language)->active()->get(['title', 'url']) as $menu) {
            $key = $this->keyFromUrl((string) $menu->url);
            if ($key !== '') $pages[$key] = $menu->title;
        }
        return $pages;
    }

    private function keyFromUrl(string $url): string
    {
        if ($url === '' || $url === '#') return '';
        $parts = parse_url($url); parse_str((string) ($parts['query'] ?? ''), $query);
        return ! empty($query['key']) ? $this->key((string) $query['key']) : $this->key((string) preg_replace('/\.php$/i', '', basename((string) ($parts['path'] ?? $url))));
    }

    private function previewUrl(string $key, string $language): string
    {
        if (str_starts_with($key, '__')) return url('/').'?'.http_build_query(['lang' => $language, 'pb_preview' => 1]);
        $map = ['index' => '/', 'about' => '/about', 'articles' => '/articles', 'article_detail' => '/articles', 'certificates' => '/certificates', 'trainings' => '/trainings', 'gallery' => '/gallery', 'contact' => '/contact', 'profile' => '/profile'];
        $path = $map[$key] ?? '/page?key='.urlencode($key);
        return url($path).(str_contains($path, '?') ? '&' : '?').http_build_query(['lang' => $language, 'pb_preview' => 1]);
    }

    private function editorUrl(string $key,string $language): string
    {
        $url=$this->previewUrl($key,$language);
        return $url.'&pb_editor=1&pb_page='.urlencode($key);
    }

    private function language(Request $request): string { return AdminLanguage::selected($request); }
    private function key(string $key): string { return preg_replace('/[^a-z0-9_-]/', '', Str::lower(trim($key))) ?: 'index'; }
    private function decode(?string $json): array { $data = json_decode((string) $json, true); return is_array($data) ? $data : []; }

    private function applyNodeContent(array &$node, array $content): void
    {
        $node['content']=$content;
        if(array_key_exists('title',$content))$node['title']=$content['title'];
        if(array_key_exists('eyebrow',$content))$node['subtitle']=$content['eyebrow'];
        if(array_key_exists('subtitle',$content))$node['subtitle']=$content['subtitle'];
        if(array_key_exists('html',$content))$node['body']=$content['html'];
        if(array_key_exists('text',$content))$node['body']=$content['text'];
        if(array_key_exists('button_text',$content))$node['button_text']=$content['button_text'];
        if(array_key_exists('button_url',$content))$node['button_url']=$content['button_url'];
    }
}
