@extends('layouts.admin')
@section('title', 'Block redaktoru')
@section('page_title', 'Block redaktoru')

@push('styles')
<style>
.pb-head,.pb-publish,.pb-actions,.pb-tabs,.pb-patterns{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.pb-head{justify-content:space-between;margin-bottom:18px}.pb-publish .form-row{min-width:260px}.pb-version{padding:8px 12px;border-radius:99px;background:#eaf9ef;color:#1f7a4d;font-size:12px;font-weight:900}.pb-shell{display:grid;grid-template-columns:410px minmax(0,1fr);gap:20px;align-items:start}.pb-form,.pb-list,.pb-revisions{display:grid;gap:13px}.pb-two,.pb-settings{display:grid;grid-template-columns:1fr 1fr;gap:12px}.pb-tabs{margin-bottom:18px;max-height:116px;overflow:auto}.pb-tabs a{padding:8px 11px;border:1px solid var(--admin-line);border-radius:99px;background:#fff;font-size:12px;font-weight:900}.pb-tabs a.active{background:var(--admin-accent);border-color:var(--admin-accent)}.pb-item,.pb-revision{display:grid;grid-template-columns:40px minmax(0,1fr) auto;gap:12px;align-items:center;border:1px solid var(--admin-line-2);border-radius:15px;padding:13px;background:#fff}.pb-item.dragging{opacity:.45}.pb-item.is-child{margin-left:calc(var(--depth) * 26px);border-left:4px solid #75d6c8}.pb-handle{cursor:grab;text-align:center;color:#718084}.pb-item h3{margin:0;font-size:15px}.pb-item p,.pb-revision small{margin:4px 0 0;color:var(--admin-muted);font-size:12px}.pb-actions{justify-content:flex-end}.pb-actions form{margin:0}.pb-actions .btn{padding:8px 10px}.pb-empty{padding:28px;text-align:center;border:1px dashed var(--admin-line);border-radius:15px;color:var(--admin-muted)}.pb-note{font-size:12px;color:var(--admin-muted);line-height:1.5}.pb-revisions{margin-top:22px}.pb-revision{grid-template-columns:minmax(0,1fr) auto}.pb-schema-fields{display:grid;gap:12px;padding:14px;border:1px solid var(--admin-line-2);border-radius:14px;background:#f8fcfb}.pb-schema-fields[hidden]{display:none}.pb-system{padding:12px;border-radius:12px;background:#fff7dd;color:#765300;font-size:12px;font-weight:800}.pb-pattern{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;align-items:center;border:1px solid var(--admin-line-2);border-radius:12px;padding:10px}.pb-pattern strong,.pb-pattern small{display:block}.pb-pattern small{color:var(--admin-muted)}details.pb-save-pattern{margin-top:7px}details.pb-save-pattern summary{cursor:pointer;font-size:12px;font-weight:900;color:#47646a}.pb-inline-form{display:grid;grid-template-columns:1fr auto;gap:7px;margin-top:8px}@media(max-width:1100px){.pb-shell{grid-template-columns:1fr}.pb-two,.pb-settings{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@if(session('status'))<div class="alert alert-ok">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<div class="pb-head">
    <form class="pb-publish" method="post" action="{{ route('admin.page-builder.publish') }}">
        @csrf
        <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><input type="hidden" name="page_key" value="{{ $pageKey }}">
        <div class="form-row"><label>Dəyişiklik qeydi</label><input name="change_note" maxlength="255" placeholder="Blok strukturu yeniləndi"></div>
        <button class="btn btn-primary" type="submit"><i class="ti ti-world-upload"></i> Dərc et</button>
        <span class="pb-version">{{ $publication ? 'Public v'.$publication->version : 'Hələ dərc edilməyib' }}</span>
    </form>
    <a class="btn btn-light" href="{{ $previewUrl }}" target="_blank" rel="noopener"><i class="ti ti-eye"></i> Draft preview</a>
</div>

<div class="pb-shell">
    <section class="card">
        <h2>{{ $edit ? 'Bloku redaktə et' : 'Yeni blok' }}</h2>
        <form method="post" action="{{ route('admin.page-builder.store') }}" enctype="multipart/form-data" class="pb-form" id="blockForm">
            @csrf
            <input type="hidden" name="id" value="{{ $edit?->id }}"><input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
            <div class="form-row"><label>Səhifə / template hissəsi</label><select name="page_key">@foreach($pages as $key=>$title)<option value="{{ $key }}" @selected($pageKey===$key)>{{ $title }}</option>@endforeach</select></div>
            @if($edit && ($schemas[$edit->block_type]['system'] ?? false))
                <input type="hidden" name="block_type" id="blockType" value="{{ $edit->block_type }}">
                <div class="pb-system"><i class="ti ti-lock"></i> Bu sistem blokudur. Mövcud public modulunu block tree daxilində təmsil edir; tipi dəyişdirilmir.</div>
            @else
                <div class="form-row"><label>Blok tipi</label><select name="block_type" id="blockType">@foreach($insertableTypes as $key=>$label)<option value="{{ $key }}" @selected(old('block_type',$edit->block_type??'rich_text')===$key)>{{ $label }}</option>@endforeach</select></div>
            @endif

            <div class="pb-two">
                <div class="form-row"><label>Parent blok</label><select name="parent_block_uuid" id="parentBlock"><option value="">Root səviyyə</option>@foreach($parentBlocks as $parent)<option value="{{ $parent->block_uuid }}" data-type="{{ $parent->block_type }}" @selected(old('parent_block_uuid',$edit?->parent_block_uuid)===$parent->block_uuid)>{{ $parent->title ?: ($blockTypes[$parent->block_type]??$parent->block_type) }}</option>@endforeach</select></div>
                <div class="form-row"><label>Slot</label><select name="slot_key" id="slotKey"><option value="default">default</option></select></div>
            </div>
            <div class="pb-two">
                <div class="form-row"><label>Region</label><select name="region_key"><option value="main" @selected(($edit->region_key??'main')==='main')>main</option><option value="template" @selected(($edit->region_key??'')==='template')>template</option><option value="sidebar" @selected(($edit->region_key??'')==='sidebar')>sidebar</option></select></div>
                <div class="form-row"><label>Sıra</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order',$edit->sort_order??0) }}"></div>
            </div>

            @foreach($schemas as $type => $schema)
                <div class="pb-schema-fields" data-schema="{{ $type }}" hidden>
                    <strong>{{ $schema['label'] }} sahələri</strong>
                    @forelse($schema['fields'] as $field)
                        @php
                            $value = old('content.'.$field['key'], $content[$field['key']] ?? ($field['default'] ?? ''));
                        @endphp
                        <div class="form-row">
                            <label>{{ $field['label'] }} @if($field['required']) * @endif</label>
                            @if($field['type']==='checkbox')
                                <label class="check"><input type="checkbox" name="content[{{ $field['key'] }}]" value="1" @checked((bool)$value)> Aktiv</label>
                            @elseif($field['type']==='textarea' || $field['type']==='richtext')
                                <textarea name="content[{{ $field['key'] }}]" rows="{{ $field['type']==='richtext'?8:4 }}">{{ $value }}</textarea>
                            @elseif($field['type']==='repeater')
                                <textarea name="content[{{ $field['key'] }}]" rows="8" placeholder='[{"title":"...","text":"..."}]'>{{ is_array($value) ? json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : $value }}</textarea>
                                <p class="pb-note">Strukturlaşdırılmış JSON massivi. Sahələr: {{ implode(', ', $field['columns']) }}. Raw HTML qəbul edilmir.</p>
                            @elseif($field['type']==='select')
                                <select name="content[{{ $field['key'] }}]">@foreach($field['options'] as $key=>$label)<option value="{{ $key }}" @selected((string)$value===(string)$key)>{{ $label }}</option>@endforeach</select>
                            @else
                                <input type="{{ $field['type']==='number'?'number':'text' }}" name="content[{{ $field['key'] }}]" value="{{ $value }}">
                            @endif
                        </div>
                    @empty
                        <span class="pb-note">Bu layout blokunun məzmunu alt bloklardan yaranır.</span>
                    @endforelse
                </div>
            @endforeach

            <div class="form-row"><label>Şəkil</label><input type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.gif,.svg">@if($edit?->image_path)<img class="preview-img" src="{{ asset(ltrim($edit->image_path,'/')) }}" alt="">@endif</div>
            @if(!($edit && ($schemas[$edit->block_type]['system'] ?? false)))
            <h2>Design token presetləri</h2>
            <div class="pb-settings">
                <div class="form-row"><label>Rəng mövzusu</label><select name="theme">@foreach(['surface'=>'Ağ','muted'=>'Açıq boz','brand'=>'Brend','dark'=>'Tünd'] as $key=>$label)<option value="{{ $key }}" @selected($settings['theme']===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-row"><label>Layout</label><select name="layout">@foreach(['card'=>'Kart','wide'=>'Geniş','centered'=>'Mərkəzli'] as $key=>$label)<option value="{{ $key }}" @selected($settings['layout']===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-row"><label>Alignment</label><select name="align">@foreach(['left'=>'Sol','center'=>'Mərkəz'] as $key=>$label)<option value="{{ $key }}" @selected($settings['align']===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-row"><label>Radius</label><select name="radius">@foreach([0,12,18,24,32] as $value)<option value="{{ $value }}" @selected((int)$settings['radius']===$value)>{{ $value }} px</option>@endforeach</select></div>
                <div class="form-row"><label>Kölgə</label><select name="shadow">@foreach(['none'=>'Yoxdur','soft'=>'Yumşaq','strong'=>'Güclü'] as $key=>$label)<option value="{{ $key }}" @selected($settings['shadow']===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-row"><label>Animasiya</label><select name="animation">@foreach(['none'=>'Yoxdur','fade-up'=>'Fade up','zoom'=>'Zoom'] as $key=>$label)<option value="{{ $key }}" @selected($settings['animation']===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-row"><label>Şəkil mövqeyi</label><select name="image_position">@foreach(['left'=>'Sol','right'=>'Sağ'] as $key=>$label)<option value="{{ $key }}" @selected($settings['image_position']===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-row"><label>Max width</label><select name="max_width">@foreach(['full'=>'Tam','boxed'=>'Boxed','narrow'=>'Dar'] as $key=>$label)<option value="{{ $key }}" @selected($settings['max_width']===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-row"><label>Şaquli boşluq</label><select name="spacing">@foreach(['small'=>'Kiçik','medium'=>'Orta','large'=>'Böyük'] as $key=>$label)<option value="{{ $key }}" @selected($settings['spacing']===$key)>{{ $label }}</option>@endforeach</select></div>
            </div>
            @endif
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$edit->is_active??true))> Aktiv</label>
            <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy"></i> Draft saxla</button>
            @if($edit)<a class="btn btn-light" href="{{ route('admin.page-builder.index',['lang_code'=>$selectedLanguage,'page'=>$pageKey]) }}">Yeni blok</a>@endif
        </form>
    </section>

    <section class="card">
        <h2>{{ $pages[$pageKey]??$pageKey }} blokları</h2>
        <div class="pb-tabs">@foreach($pages as $key=>$title)<a class="{{ $pageKey===$key?'active':'' }}" href="{{ route('admin.page-builder.index',['lang_code'=>$selectedLanguage,'page'=>$key]) }}">{{ $title }}</a>@endforeach</div>
        @if($blockTree->isNotEmpty())
            <div class="pb-list" id="pbList">@foreach($blockTree as $node) @php
                $block = $node['block'];
            @endphp
                <article class="pb-item {{ $node['depth']?'is-child':'' }}" style="--depth:{{ $node['depth'] }}" draggable="{{ $node['depth']?'false':'true' }}" data-id="{{ $block->id }}">
                    <div class="pb-handle"><i class="ti {{ $node['depth']?'ti-corner-down-right':'ti-grip-vertical' }}"></i></div>
                    <div><h3>{{ $block->title?:($blockTypes[$block->block_type]??'Blok') }}</h3><p>{{ $blockTypes[$block->block_type]??$block->block_type }} · {{ $block->region_key }}/{{ $block->slot_key }} · #{{ $block->sort_order }} · {{ $block->is_active?'aktiv':'passiv' }}</p></div>
                    <div class="pb-actions">
                        <a class="btn btn-light" title="Redaktə et" href="{{ route('admin.page-builder.index',['lang_code'=>$selectedLanguage,'page'=>$pageKey,'edit'=>$block->id]) }}"><i class="ti ti-pencil"></i></a>
                        <form method="post" action="{{ route('admin.page-builder.duplicate',$block) }}">@csrf<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><button class="btn btn-light" title="Ağacı kopyala"><i class="ti ti-copy"></i></button></form>
                        @if(!($schemas[$block->block_type]['system']??false))<form method="post" action="{{ route('admin.page-builder.destroy',$block) }}" onsubmit="return confirm('Blok və alt blokları silinsin?')">@csrf @method('delete')<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><button class="btn btn-danger"><i class="ti ti-trash"></i></button></form>@endif
                    </div>
                    @if(!($schemas[$block->block_type]['system']??false))<div></div><div><details class="pb-save-pattern"><summary>Pattern kimi saxla</summary><form class="pb-inline-form" method="post" action="{{ route('admin.page-builder.pattern.store',$block) }}">@csrf<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><input name="name" required placeholder="Pattern adı"><button class="btn btn-light">Saxla</button></form></details></div>@endif
                </article>
            @endforeach</div>
        @else<div class="pb-empty">Bu səhifə üçün draft blok yoxdur.</div>@endif

        @if($patterns->isNotEmpty())<div class="pb-revisions"><h2>Reusable patterns</h2>@foreach($patterns as $pattern)<div class="pb-pattern"><div><strong>{{ $pattern->name }}</strong><small>{{ $blockTypes[$pattern->root_type]??$pattern->root_type }} · {{ $pattern->category }}</small></div><form method="post" action="{{ route('admin.page-builder.pattern.insert',$pattern) }}">@csrf<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><input type="hidden" name="page_key" value="{{ $pageKey }}"><button class="btn btn-light">Əlavə et</button></form></div>@endforeach</div>@endif
        @if($revisions->isNotEmpty())<div class="pb-revisions"><h2>Public versiyalar</h2>@foreach($revisions as $revision)<div class="pb-revision"><div><strong>v{{ $revision->version }}</strong><p>{{ $revision->change_note?:'Qeyd yoxdur' }}</p><small>{{ $revision->created_at?->format('d.m.Y H:i') }}</small></div>@if(!$publication||$publication->version!==$revision->version)<form method="post" action="{{ route('admin.page-builder.restore',$revision) }}">@csrf<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><button class="btn btn-light">Bərpa et</button></form>@endif</div>@endforeach</div>@endif
    </section>
</div>

<script>
(() => {
    const schemas=@json($schemas), type=document.getElementById('blockType'), parent=document.getElementById('parentBlock'), slot=document.getElementById('slotKey');
    const currentSlot=@json(old('slot_key',$edit?->slot_key??'default'));
    function showSchema(){document.querySelectorAll('[data-schema]').forEach(el=>{const active=el.dataset.schema===type.value;el.hidden=!active;el.querySelectorAll('input,textarea,select').forEach(field=>field.disabled=!active)})}
    function showSlots(){const option=parent.options[parent.selectedIndex], parentType=option?.dataset.type, slots=parentType?(schemas[parentType]?.slots||[]):['default'];slot.innerHTML='';(slots.length?slots:['default']).forEach(value=>{const o=new Option(value,value,value===currentSlot,value===currentSlot);slot.add(o)})}
    type?.addEventListener('change',showSchema);parent?.addEventListener('change',showSlots);showSchema();showSlots();
    const list=document.getElementById('pbList');if(!list)return;let dragged=null;
    list.querySelectorAll('.pb-item[draggable="true"]').forEach(item=>{item.addEventListener('dragstart',()=>{dragged=item;item.classList.add('dragging')});item.addEventListener('dragend',()=>{item.classList.remove('dragging');saveOrder()})});
    list.addEventListener('dragover',event=>{event.preventDefault();const items=[...list.querySelectorAll('.pb-item[draggable="true"]:not(.dragging)')];const after=items.reduce((best,item)=>{const box=item.getBoundingClientRect(),offset=event.clientY-box.top-box.height/2;return offset<0&&offset>best.offset?{offset,el:item}:best},{offset:-Infinity,el:null}).el;if(dragged)after?list.insertBefore(dragged,after):list.appendChild(dragged)});
    async function saveOrder(){const data=new FormData();data.append('_token',@json(csrf_token()));data.append('lang_code',@json($selectedLanguage));data.append('page_key',@json($pageKey));data.append('order',JSON.stringify([...list.querySelectorAll('.pb-item[draggable="true"]')].map(item=>item.dataset.id)));await fetch(@json(route('admin.page-builder.sort')),{method:'POST',body:data})}
})();
</script>
@endsection
