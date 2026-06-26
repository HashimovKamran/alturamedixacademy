@extends('layouts.admin')
@section('title', 'Visual Page Builder')
@section('page_title', 'Visual Page Builder')
@section('content')
<div id="altura-page-builder-root"
     data-api-base="{{ url('/admin/page-editor/api') }}"
     data-canvas-url="{{ url('/admin/page-editor/canvas') }}?lang_code={{ urlencode($language) }}&page={{ urlencode($pageKey) }}"
     data-preview-url="{{ $previewUrl }}"
     data-page-key="{{ $pageKey }}"
     data-language="{{ $language }}"
     data-can-edit="{{ $canEdit ? '1' : '0' }}"
     data-can-publish="{{ $canPublish ? '1' : '0' }}"></div>
@endsection
@push('scripts')
@vite('resources/js/app.js')
@endpush
