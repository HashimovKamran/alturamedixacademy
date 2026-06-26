@extends('layouts.admin')
@section('title','Vizual səhifə redaktoru')
@section('page_title','Vizual səhifə redaktoru')
@push('styles')
<style>
.vpe-top{display:flex;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:14px}.vpe-page{min-width:260px}.vpe-status{margin-left:auto;display:flex;align-items:center;gap:8px}.vpe-version{padding:9px 12px;border-radius:999px;background:#eaf9ef;color:#1f7a4d;font-size:12px;font-weight:900}.vpe-frame-shell{background:#dce9e8;border:1px solid #cddfdd;border-radius:18px;padding:10px;box-shadow:0 18px 50px rgba(7,23,40,.09)}.vpe-frame{display:block;width:100%;height:calc(100vh - 210px);min-height:680px;border:0;border-radius:12px;background:#fff}.vpe-help{margin:0 0 12px;color:#63747a;font-size:12px;font-weight:750}.vpe-publish{display:flex;gap:8px}.vpe-publish input{width:220px}
</style>
@endpush
@section('content')
@if(session('status'))<div class="alert alert-ok">{{ session('status') }}</div>@endif
<div class="vpe-top">
    <form method="get" action="{{ route('admin.page-builder.index') }}" class="vpe-page">
        <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
        <label>Səhifə</label><select name="page" onchange="this.form.submit()">@foreach($pages as $key=>$title)<option value="{{ $key }}" @selected($pageKey===$key)>{{ $title }}</option>@endforeach</select>
    </form>
    <form class="vpe-publish" method="post" action="{{ route('admin.page-builder.publish') }}">@csrf<input type="hidden" name="lang_code" value="{{ $selectedLanguage }}"><input type="hidden" name="page_key" value="{{ $pageKey }}"><input name="change_note" placeholder="Dəyişiklik qeydi"><button class="btn btn-primary" type="submit"><i class="ti ti-world-upload"></i>Dərc et</button></form>
    <div class="vpe-status"><span class="vpe-version">{{ $publication?'Public v'.$publication->version:'Dərc edilməyib' }}</span><a class="btn btn-light" target="_blank" href="{{ preg_replace('/([?&])pb_editor=1(&|$)/','$1',$editorUrl) }}">Public preview</a></div>
</div>
<p class="vpe-help">Mətni birbaşa yerində dəyişin. Narıncı “+” ilə blok əlavə edin; blok üzərindəki toolbar ilə sırala, kopyala, sil və ayarları dəyişin.</p>
<div class="vpe-frame-shell"><iframe class="vpe-frame" src="{{ $editorUrl }}" title="Səhifə canvas"></iframe></div>
@endsection
