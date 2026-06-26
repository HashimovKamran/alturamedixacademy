@php
    $compositionBlocks = $pageBuilderBlocks ?? collect();
    $compositionDocument = $pageBuilderDocument
        ?? app(\App\PageBuilder\Services\PageDocumentService::class)->fromLegacyBlocks(collect($compositionBlocks));
    $editorPageKey = $compositionDocument['page_key'] ?? $compositionBlocks->first()?->page_key ?? request()->query('pb_page', 'index');
    $blockEditor = request()->boolean('pb_editor') && (int) request()->session()->get('admin_user_id', 0) > 0
        && request()->query('pb_page', 'index') === $editorPageKey;
    $templatePart = (bool) ($templatePart ?? false);
    $editorTypes = collect(app(\App\PageBuilder\Registry\BlockDefinitionRegistry::class)->all('sections'))
        ->reject(fn ($definition) => (bool) ($definition['system'] ?? false))
        ->mapWithKeys(fn ($definition, $key) => [$key => $definition['label'] ?? $key])
        ->all();
    $rendererContext = get_defined_vars();
@endphp

@if(!empty($compositionDocument['sections']))
    @if(!$templatePart)
        <div class="pb-public-wrap {{ $blockEditor ? 'pb-editor-canvas' : '' }}" data-editor-page="{{ $editorPageKey }}" data-document-mode="1">
    @elseif($blockEditor)
        <div class="pb-editor-canvas" data-editor-page="{{ $editorPageKey }}" data-document-mode="1">
    @endif
        {!! app(\App\PageBuilder\Rendering\Renderer::class)->renderDocument($compositionDocument, $rendererContext) !!}
    @if(!$templatePart || $blockEditor)
        </div>
    @endif
@elseif($blockEditor)
    <div class="pb-public-wrap pb-editor-canvas" data-editor-page="{{ $editorPageKey }}" data-document-mode="1">
        @include('public.partials.block-add-control', ['afterUuid' => null, 'editorTypes' => $editorTypes])
    </div>
@endif

@if($blockEditor)
@once
<script>
(() => {
const canvas=document.querySelector('[data-editor-page]');if(!canvas)return;
const token=document.querySelector('meta[name="csrf-token"]')?.content;
const post=async(url,data)=>{const body=data instanceof FormData?data:new FormData();if(!(data instanceof FormData))Object.entries(data).forEach(([k,v])=>body.append(k,v??''));body.append('_token',token);const response=await fetch(url,{method:'POST',body,headers:{Accept:'application/json'}});if(!response.ok)throw new Error(await response.text());return response.json()};
const actionUrl=@json(route('admin.page-builder.editor-action')), inlineUrl=@json(route('admin.page-builder.inline')), entityUrl=@json(route('admin.page-builder.entity-inline')), settingsUrl=@json(route('admin.page-builder.editor-settings'));
const payload=data=>Object.assign({page_key:canvas.dataset.editorPage,document_mode:canvas.dataset.documentMode||1},data);
const parseMeta=el=>{try{return JSON.parse(el.getAttribute('data-editor-section')||el.getAttribute('data-editor-block')||'{}')}catch(error){return {}}};
canvas.addEventListener('click',event=>{const target=event.target.closest('[data-editor-section],[data-editor-block]');if(!target)return;const meta=parseMeta(target);window.parent?.postMessage({type:target.hasAttribute('data-editor-section')?'section-selected':'block-selected',uuid:target.dataset.blockUuid,id:target.dataset.sectionId||target.dataset.blockId||'',blockType:meta.type||'',name:meta.name||''},'*')});
canvas.querySelectorAll('[data-inline-field]').forEach(el=>{el.contentEditable='true';el.addEventListener('blur',async()=>{const block=el.closest('[data-block-uuid]');if(!block)return;await post(inlineUrl,payload({block_uuid:block.dataset.blockUuid,field:el.dataset.inlineField,value:el.dataset.inlineType==='richtext'?el.innerHTML:el.textContent}));el.classList.add('saved');setTimeout(()=>el.classList.remove('saved'),700)})});
const entityTimers=new WeakMap();const saveEntity=async el=>{const copy=el.cloneNode(true);copy.querySelectorAll('i,svg').forEach(icon=>icon.remove());await post(entityUrl,{entity:el.dataset.entity,entity_id:el.dataset.entityId,field:el.dataset.entityField,value:el.dataset.entityType==='richtext'?el.innerHTML:copy.textContent.trim(),richtext:el.dataset.entityType==='richtext'?1:0,lang:@json($lang)});el.classList.add('saved');setTimeout(()=>el.classList.remove('saved'),700)};
canvas.querySelectorAll('[data-entity]').forEach(el=>{el.contentEditable='true';el.setAttribute('data-editor-inline','');el.addEventListener('click',event=>{event.preventDefault();event.stopPropagation()});el.addEventListener('input',()=>{clearTimeout(entityTimers.get(el));entityTimers.set(el,setTimeout(()=>saveEntity(el),550))});el.addEventListener('blur',()=>{clearTimeout(entityTimers.get(el));saveEntity(el)})});
document.addEventListener('click',async event=>{const button=event.target.closest('[data-block-action]');if(!button)return;const block=button.closest('[data-block-uuid]');if(!block)return;const action=button.dataset.blockAction;if(action==='delete'&&!confirm('Blok silinsin?'))return;await post(actionUrl,payload({block_uuid:block.dataset.blockUuid,action}));location.reload()});
canvas.querySelectorAll('[data-block-settings]').forEach(form=>form.addEventListener('submit',async event=>{event.preventDefault();const block=form.closest('[data-block-uuid]');const data=new FormData(form);data.append('block_uuid',block.dataset.blockUuid);data.append('page_key',canvas.dataset.editorPage);data.append('document_mode',canvas.dataset.documentMode||1);await post(settingsUrl,data);location.reload()}));
canvas.querySelectorAll('[data-add-block-form]').forEach(form=>form.addEventListener('submit',async event=>{event.preventDefault();const data=new FormData(form);data.append('page_key',canvas.dataset.editorPage);data.append('document_mode',canvas.dataset.documentMode||1);await post(actionUrl,data);location.reload()}));
})();
</script>
@endonce
@endif
