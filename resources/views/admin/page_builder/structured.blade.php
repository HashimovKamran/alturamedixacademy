@extends('layouts.admin')
@section('title', 'Səhifə redaktoru')
@section('page_title', 'Səhifə redaktoru')

@push('styles')
<style>
.pb-head,.pb-publish,.pb-actions,.pb-tabs{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.pb-head{justify-content:space-between;margin-bottom:18px}.pb-publish .form-row{min-width:280px}.pb-version{padding:8px 12px;border-radius:99px;background:#eaf9ef;color:#1f7a4d;font-size:12px;font-weight:900}.pb-shell{display:grid;grid-template-columns:390px minmax(0,1fr);gap:20px;align-items:start}.pb-form,.pb-list,.pb-revisions{display:grid;gap:13px}.pb-two,.pb-settings{display:grid;grid-template-columns:1fr 1fr;gap:12px}.pb-tabs{margin-bottom:18px}.pb-tabs a{padding:8px 11px;border:1px solid var(--admin-line);border-radius:99px;background:#fff;font-size:12px;font-weight:900}.pb-tabs a.active{background:var(--admin-accent);border-color:var(--admin-accent)}.pb-item,.pb-revision{display:grid;grid-template-columns:40px minmax(0,1fr) auto;gap:12px;align-items:center;border:1px solid var(--admin-line-2);border-radius:15px;padding:13px;background:#fff}.pb-item.dragging{opacity:.45}.pb-handle{cursor:grab;text-align:center;color:#718084}.pb-item h3{margin:0;font-size:15px}.pb-item p,.pb-revision small{margin:4px 0 0;color:var(--admin-muted);font-size:12px}.pb-actions{justify-content:flex-end}.pb-actions .btn{padding:8px 10px}.pb-preview{width:90px;height:60px;object-fit:cover;border-radius:10px;border:1px solid var(--admin-line)}.pb-empty{padding:28px;text-align:center;border:1px dashed var(--admin-line);border-radius:15px;color:var(--admin-muted)}.pb-note{font-size:12px;color:var(--admin-muted);line-height:1.5}.pb-revisions{margin-top:22px}.pb-revision{grid-template-columns:minmax(0,1fr) auto}.pb-revision form{margin:0}@media(max-width:1100px){.pb-shell{grid-template-columns:1fr}.pb-two,.pb-settings{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@if(session('status'))<div class="alert alert-ok">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<div class="pb-head">
    <form class="pb-publish" method="post" action="{{ route('admin.page-builder.publish') }}">
        @csrf
        <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
        <input type="hidden" name="page_key" value="{{ $pageKey }}">
        <div class="form-row"><label>Dəyişiklik qeydi</label><input name="change_note" maxlength="255" placeholder="Hero və CTA yeniləndi"></div>
        <button class="btn btn-primary" type="submit"><i class="ti ti-world-upload"></i> Dərc et</button>
        <span class="pb-version">{{ $publication ? 'Public v'.$publication->version : 'Hələ dərc edilməyib' }}</span>
    </form>
    <a class="btn btn-light" href="{{ $previewUrl }}" target="_blank" rel="noopener"><i class="ti ti-eye"></i> Draft preview</a>
</div>

<div class="pb-shell">
    <section class="card">
        <h2>{{ $edit ? 'Bloku redaktə et' : 'Yeni blok' }}</h2>
        <form method="post" action="{{ route('admin.page-builder.store') }}" enctype="multipart/form-data" class="pb-form">
            @csrf
            <input type="hidden" name="id" value="{{ $edit?->id }}"><input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
            <div class="form-row"><label>Səhifə</label><select name="page_key">@foreach($pages as $key=>$title)<option value="{{ $key }}" @selected($pageKey===$key)>{{ $title }}</option>@endforeach</select></div>
            <div class="form-row"><label>Blok tipi</label><select name="block_type" id="blockType">@foreach($blockTypes as $key=>$label)<option value="{{ $key }}" @selected(old('block_type',$edit->block_type??'text')===$key)>{{ $label }}</option>@endforeach</select></div>
            <div class="form-row"><label>Başlıq</label><input name="title" maxlength="255" value="{{ old('title',$edit->title??'') }}"></div>
            <div class="form-row"><label>Alt başlıq</label><input name="subtitle" maxlength="255" value="{{ old('subtitle',$edit->subtitle??'') }}"></div>
            <div class="form-row"><label>Mətn</label><textarea name="body" rows="8">{{ old('body',$edit->body??'') }}</textarea><p class="pb-note">Kart və FAQ üçün hər sətir: Başlıq | Mətn. Məlumat struktur JSON kimi saxlanılır.</p></div>
            <div class="pb-two"><div class="form-row"><label>Düymə mətni</label><input name="button_text" value="{{ old('button_text',$edit->button_text??'') }}"></div><div class="form-row"><label>Düymə linki</label><input name="button_url" value="{{ old('button_url',$edit->button_url??'#') }}"></div></div>
            <div class="pb-two"><div class="form-row"><label>Şəkil</label><input type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.gif,.svg">@if($edit?->image_path)<img class="pb-preview" src="{{ asset(ltrim($edit->image_path,'/')) }}" alt="">@endif</div><div class="form-row"><label>Sıra</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order',$edit->sort_order??0) }}"></div></div>
            <h2>Görünüş presetləri</h2>
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
            <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$edit->is_active??true))> Aktiv</label>
            <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy"></i> Draft saxla</button>
            @if($edit)<a class="btn btn-light" href="{{ route('admin.page-builder.index',['lang_code'=>$selectedLanguage,'page'=>$pageKey]) }}">Yeni blok</a>@endif
        </form>
    </section>

    <section class="card">
        <h2>{{ $pages[$pageKey]??$pageKey }} blokları</h2>
        <div class="pb-tabs">@foreach($pages as $key=>$title)<a class="{{ $pageKey===$key?'active':'' }}" href="{{ route('admin.page-builder.index',['lang_code'=>$selectedLanguage,'page'=>$key]) }}">{{ $title }}</a>@endforeach</div>
        @if($blocks->isNotEmpty())
            <div class="pb-list" id="pbList">@foreach($blocks as $block)
                <article class="pb-item" draggable="true" data-id="{{ $block->id }}"><div class="pb-handle"><i class="ti ti-grip-vertical"></i></div><div><h3>{{ $block->title?:($blockTypes[$block->block_type]??'Blok') }}</h3><p>{{ $blockTypes[$block->block_type]??$block->block_type }} · #{{ $block->sort_order }} · {{ $block->is_active?'aktiv':'passiv' }}</p></div><div class="pb-actions"><a class="btn btn-light" href="{{ route('admin.page-builder.index',['lang_code'=>$selectedLanguage,'page'=>$pageKey,'edit'=>$block->id]) }}"><i class="ti ti-pencil"></i></a><form method="post" action="{{ route('admin.page-builder.duplicate',$block) }}">@csrf<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><button class="btn btn-light"><i class="ti ti-copy"></i></button></form><form method="post" action="{{ route('admin.page-builder.destroy',$block) }}" onsubmit="return confirm('Blok draft-dan silinsin?')">@csrf @method('delete')<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><button class="btn btn-danger"><i class="ti ti-trash"></i></button></form></div></article>
            @endforeach</div>
        @else<div class="pb-empty">Bu səhifə üçün draft blok yoxdur.</div>@endif

        @if($revisions->isNotEmpty())<div class="pb-revisions"><h2>Public versiyalar</h2>@foreach($revisions as $revision)<div class="pb-revision"><div><strong>v{{ $revision->version }}</strong><p>{{ $revision->change_note?:'Qeyd yoxdur' }}</p><small>{{ $revision->created_at?->format('d.m.Y H:i') }}</small></div>@if(!$publication||$publication->version!==$revision->version)<form method="post" action="{{ route('admin.page-builder.restore',$revision) }}" onsubmit="return confirm('Bu versiya bərpa edilsin?')">@csrf<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><button class="btn btn-light">Bərpa et</button></form>@endif</div>@endforeach</div>@endif
    </section>
</div>

<script>
const list=document.getElementById('pbList');if(list){let dragged=null;list.querySelectorAll('.pb-item').forEach(item=>{item.addEventListener('dragstart',()=>{dragged=item;item.classList.add('dragging')});item.addEventListener('dragend',()=>{item.classList.remove('dragging');saveOrder()})});list.addEventListener('dragover',event=>{event.preventDefault();const items=[...list.querySelectorAll('.pb-item:not(.dragging)')];const after=items.reduce((best,item)=>{const box=item.getBoundingClientRect(),offset=event.clientY-box.top-box.height/2;return offset<0&&offset>best.offset?{offset,el:item}:best},{offset:-Infinity,el:null}).el;if(dragged)after?list.insertBefore(dragged,after):list.appendChild(dragged)});async function saveOrder(){const data=new FormData();data.append('_token','{{ csrf_token() }}');data.append('lang_code','{{ $selectedLanguage }}');data.append('page_key','{{ $pageKey }}');data.append('order',JSON.stringify([...list.querySelectorAll('.pb-item')].map(item=>item.dataset.id)));await fetch('{{ route('admin.page-builder.sort') }}',{method:'POST',body:data})}}
</script>
@endsection
