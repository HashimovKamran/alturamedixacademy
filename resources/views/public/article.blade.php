@extends('layouts.public')

@php
    $ui = \App\Support\PublicUiText::all($lang);
@endphp

@section('title', ($article?->meta_title ?: $article?->title ?? $ui['article_not_found']) . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))
@section('meta_description', $article?->meta_description ?: $article?->excerpt ?? '')
@section('robots', $article?->robots ?? 'index,follow')
@section('og_type', 'article')
@if($article?->meta_image || $article?->cover_image)
    @section('meta_image', asset(ltrim($article->meta_image ?: $article->cover_image, '/')))
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('css/article-reader.css') }}">
@endpush

@section('content')
@include('public.partials.composition')
@endsection