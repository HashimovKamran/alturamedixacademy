@extends('layouts.admin')
@section('title', 'Visual page editor')
@section('page_title', 'Visual page editor')

@php
    $sections = collect($document['sections'] ?? []);
    $order = collect($document['order'] ?? [])->filter(fn ($id) => $sections->has($id))->values();
    $layers = $order->map(fn ($id) => ['id' => $id, 'node' => $sections[$id]])->values();
    $library = collect($insertableTypes ?? [])
        ->reject(fn ($definition) => (bool) ($definition['system'] ?? false))
        ->filter(fn ($definition) => (bool) ($definition['exists'] ?? true))
        ->groupBy(fn ($definition) => $definition['category'] ?? 'Content', true);
@endphp

@push('styles')
<style>
.v2-top{display:flex;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:14px}.v2-top .field{min-width:240px}.v2-top label,.v2-panel label{display:block;margin-bottom:6px;color:#607078;font-size:12px;font-weight:800}.v2-status{margin-left:auto;display:flex;align-items:center;gap:8px}.v2-pill{padding:9px 12px;border-radius:999px;background:#eaf9ef;color:#1f7a4d;font-size:12px;font-weight:900}.v2-publish{display:flex;gap:8px}.v2-publish input{width:220px}.v2-shell{display:grid;grid-template-columns:280px minmax(0,1fr)340px;gap:12px;height:calc(100vh - 190px);min-height:680px}.v2-panel,.v2-canvas{background:#fff;border:1px solid #d9e5e4;border-radius:14px;box-shadow:0 16px 42px rgba(7,23,40,.07);min-height:0}.v2-panel{display:flex;flex-direction:column;overflow:hidden}.v2-panel-head{padding:14px 14px 10px;border-bottom:1px solid #edf3f2}.v2-panel-head h2{margin:0;color:#102431;font-size:14px}.v2-scroll{padding:12px;overflow:auto}.v2-layer{width:100%;display:flex;align-items:center;gap:9px;margin-bottom:8px;padding:10px;border:1px solid #dfe9e8;border-radius:10px;background:#f8fbfb;color:#1d313a;text-align:left;font-weight:800;cursor:pointer}.v2-layer.active{border-color:#df7412;background:#fff4e8;color:#b65304}.v2-layer.dragging{opacity:.55}.v2-layer small{display:block;color:#75868d;font-size:11px;font-weight:700}.v2-empty{padding:12px;border:1px dashed #c8d8d6;border-radius:10px;color:#6f7f86;font-size:13px}.v2-library-title{margin:18px 0 8px;color:#5b6c74;font-size:11px;font-weight:900;text-transform:uppercase}.v2-add{width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:7px;padding:9px 10px;border:1px solid #dfe8e7;border-radius:10px;background:#fff;color:#1d313a;font-weight:800;cursor:pointer}.v2-add:hover{border-color:#df7412;color:#b65304}.v2-canvas{display:flex;flex-direction:column;background:#dce9e8;padding:10px}.v2-toolbar{display:flex;align-items:center;gap:8px;margin-bottom:10px}.v2-toolbar button{border:1px solid #cbd9d7;background:#fff;border-radius:9px;padding:8px 10px;color:#283b44;font-weight:800;cursor:pointer}.v2-toolbar button.active{background:#df7412;border-color:#df7412;color:#fff}.v2-frame-wrap{flex:1;display:flex;justify-content:center;min-height:0}.v2-frame{width:100%;height:100%;border:0;border-radius:10px;background:#fff;box-shadow:0 12px 35px rgba(7,23,40,.08);transition:width .18s ease}.v2-frame.tablet{width:820px}.v2-frame.mobile{width:390px}.v2-selected{padding:10px;border:1px solid #dfe9e8;border-radius:10px;background:#f8fbfb;color:#263941;font-size:13px}.v2-json{width:100%;min-height:360px;resize:vertical;border:1px solid #d8e4e2;border-radius:10px;padding:10px;font-family:Consolas,monospace;font-size:12px;line-height:1.45}.v2-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.v2-note{margin:10px 0 0;color:#6b7c83;font-size:12px;line-height:1.4}@media(max-width:1200px){.v2-shell{grid-template-columns:230px minmax(0,1fr);height:auto}.v2-shell>.v2-panel:last-child{grid-column:1/-1}.v2-canvas{min-height:720px}}@media(max-width:760px){.v2-shell{display:block}.v2-panel,.v2-canvas{margin-bottom:12px}.v2-canvas{height:680px}.v2-status{margin-left:0}.v2-frame.mobile,.v2-frame.tablet{width:100%}}
</style>
@endpush

@section('content')
@if(session('status'))<div class="alert alert-ok">{{ session('status') }}</div>@endif

<div class="v2-top">
    <form method="get" action="{{ route('admin.page-builder.index') }}" class="field">
        <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
        <label>Page</label>
        <select name="page" onchange="this.form.submit()">
            @foreach($pages as $key => $title)
                <option value="{{ $key }}" @selected($pageKey === $key)>{{ $title }}</option>
            @endforeach
        </select>
    </form>

    <form class="v2-publish" method="post" action="{{ route('admin.page-builder.publish') }}">
        @csrf
        <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
        <input type="hidden" name="page_key" value="{{ $pageKey }}">
        <input name="change_note" placeholder="Change note">
        <button class="btn btn-primary" type="submit"><i class="ti ti-world-upload"></i> Publish</button>
    </form>

    <div class="v2-status">
        <span class="v2-pill">{{ $publication ? 'Public v'.$publication->version : 'Not published' }}</span>
        <a class="btn btn-light" target="_blank" href="{{ preg_replace('/([?&])pb_editor=1(&|$)/', '$1', $editorUrl) }}">Public preview</a>
    </div>
</div>

<div class="v2-shell" data-editor-root>
    <aside class="v2-panel">
        <div class="v2-panel-head"><h2>Layers</h2></div>
        <div class="v2-scroll" id="v2Layers">
            @forelse($layers as $layer)
                @php
                    $node = $layer['node'];
                    $type = $node['type'] ?? 'section';
                    $schema = $schemas[$type] ?? [];
                    $label = $schema['label'] ?? $node['name'] ?? $type;
                @endphp
                <button type="button" class="v2-layer" draggable="true" data-layer-id="{{ $layer['id'] }}" data-layer-uuid="{{ $node['block_uuid'] ?? $layer['id'] }}">
                    <i class="ti ti-layout-board"></i>
                    <span>{{ $label }}<small>{{ $type }}</small></span>
                </button>
            @empty
                <div class="v2-empty">No sections yet.</div>
            @endforelse

            <div class="v2-library">
                @foreach($library as $category => $items)
                    <div class="v2-library-title">{{ $category }}</div>
                    @foreach($items as $type => $definition)
                        <button type="button" class="v2-add" data-add-type="{{ $type }}">
                            <span>{{ $definition['label'] ?? $type }}</span>
                            <i class="ti ti-plus"></i>
                        </button>
                    @endforeach
                @endforeach
            </div>
        </div>
    </aside>

    <main class="v2-canvas">
        <div class="v2-toolbar">
            <button type="button" class="active" data-device="desktop"><i class="ti ti-device-desktop"></i></button>
            <button type="button" data-device="tablet"><i class="ti ti-device-tablet"></i></button>
            <button type="button" data-device="mobile"><i class="ti ti-device-mobile"></i></button>
            <button type="button" id="v2Undo"><i class="ti ti-arrow-back-up"></i></button>
            <button type="button" id="v2Redo"><i class="ti ti-arrow-forward-up"></i></button>
            <button type="button" id="v2Reload"><i class="ti ti-refresh"></i></button>
        </div>
        <div class="v2-frame-wrap">
            <iframe id="v2Frame" class="v2-frame" src="{{ $editorUrl }}" title="Page canvas"></iframe>
        </div>
    </main>

    <aside class="v2-panel">
        <div class="v2-panel-head"><h2>Settings</h2></div>
        <div class="v2-scroll">
            <div class="v2-selected" id="v2Selected">Select a section in the preview or layer list.</div>
            <p class="v2-note">This JSON is the v2 draft source. Public output changes only after publish.</p>
            <form id="v2DocumentForm">
                @csrf
                <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
                <input type="hidden" name="page_key" value="{{ $pageKey }}">
                <label>Document JSON</label>
                <textarea class="v2-json" name="document" spellcheck="false">{{ json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</textarea>
                <div class="v2-actions">
                    <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy"></i> Save draft</button>
                    <button class="btn btn-light" type="button" id="v2Reset"><i class="ti ti-restore"></i> Reload draft</button>
                </div>
            </form>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(() => {
const root=document.querySelector('[data-editor-root]');if(!root)return;
const frame=document.getElementById('v2Frame');
const selected=document.getElementById('v2Selected');
const documentForm=document.getElementById('v2DocumentForm');
const textarea=documentForm.querySelector('textarea[name="document"]');
const token=documentForm.querySelector('input[name="_token"]').value;
const pageKey=documentForm.querySelector('input[name="page_key"]').value;
const post=async(url,data)=>{const body=data instanceof FormData?data:new FormData();if(!(data instanceof FormData))Object.entries(data).forEach(([key,value])=>body.append(key,value??''));body.append('_token',token);const response=await fetch(url,{method:'POST',body,headers:{Accept:'application/json'}});if(!response.ok)throw new Error(await response.text());return response.json()};
let history=[textarea.value],historyIndex=0,historyTimer=null,draggedLayer=null;
const remember=()=>{const value=textarea.value;if(value===history[historyIndex])return;history=history.slice(0,historyIndex+1);history.push(value);historyIndex=history.length-1};
const saveDocument=async()=>post(@json(route('admin.page-builder.document.save')),new FormData(documentForm));
const setSelected=(label,uuid)=>{selected.textContent=label+(uuid?' #'+uuid.slice(0,8):'');document.querySelectorAll('.v2-layer').forEach(layer=>layer.classList.toggle('active',layer.dataset.layerUuid===uuid||layer.dataset.layerId===uuid))};
textarea.addEventListener('input',()=>{clearTimeout(historyTimer);historyTimer=setTimeout(remember,250)});
document.getElementById('v2Undo').addEventListener('click',()=>{if(historyIndex<=0)return;historyIndex-=1;textarea.value=history[historyIndex]});
document.getElementById('v2Redo').addEventListener('click',()=>{if(historyIndex>=history.length-1)return;historyIndex+=1;textarea.value=history[historyIndex]});
document.querySelectorAll('[data-device]').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('[data-device]').forEach(item=>item.classList.remove('active'));button.classList.add('active');frame.classList.remove('tablet','mobile');if(button.dataset.device!=='desktop')frame.classList.add(button.dataset.device)}));
document.getElementById('v2Reload').addEventListener('click',()=>frame.contentWindow?.location.reload());
document.querySelectorAll('.v2-layer').forEach(layer=>layer.addEventListener('click',()=>{setSelected(layer.textContent.trim(),layer.dataset.layerUuid);frame.contentWindow?.postMessage({type:'select-node',uuid:layer.dataset.layerUuid},'*')}));
document.querySelectorAll('.v2-layer').forEach(layer=>{layer.addEventListener('dragstart',()=>{draggedLayer=layer;layer.classList.add('dragging')});layer.addEventListener('dragend',async()=>{if(!draggedLayer)return;draggedLayer.classList.remove('dragging');draggedLayer=null;try{const doc=JSON.parse(textarea.value);doc.order=[...document.querySelectorAll('.v2-layer')].map(item=>item.dataset.layerId);textarea.value=JSON.stringify(doc,null,2);remember();await saveDocument();frame.contentWindow?.location.reload()}catch(error){alert('Document JSON is not valid.')}})});
document.getElementById('v2Layers').addEventListener('dragover',event=>{if(!draggedLayer)return;event.preventDefault();const layers=[...document.querySelectorAll('.v2-layer:not(.dragging)')];const after=layers.reduce((best,item)=>{const box=item.getBoundingClientRect(),offset=event.clientY-box.top-box.height/2;return offset<0&&offset>best.offset?{offset,el:item}:best},{offset:-Infinity,el:null}).el;after?after.parentNode.insertBefore(draggedLayer,after):document.querySelector('.v2-library')?.before(draggedLayer)});
document.querySelectorAll('[data-add-type]').forEach(button=>button.addEventListener('click',async()=>{await post(@json(route('admin.page-builder.editor-action')),{action:'add',block_type:button.dataset.addType,page_key:pageKey,document_mode:1});location.reload()}));
documentForm.addEventListener('submit',async event=>{event.preventDefault();remember();await saveDocument();frame.contentWindow?.location.reload()});
document.getElementById('v2Reset').addEventListener('click',async()=>{const url=@json(route('admin.page-builder.document')).concat('?page_key=',encodeURIComponent(pageKey),'&lang_code=',encodeURIComponent(@json($selectedLanguage)));const response=await fetch(url,{headers:{Accept:'application/json'}});const data=await response.json();textarea.value=JSON.stringify(data.document,null,2);frame.contentWindow?.location.reload()});
window.addEventListener('message',event=>{if(!event.data||!['section-selected','block-selected'].includes(event.data.type))return;setSelected(event.data.name||event.data.blockType||event.data.type,event.data.uuid)});
})();
</script>
@endpush
