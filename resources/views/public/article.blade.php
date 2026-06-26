@extends('layouts.public')
@php
    $ui = \App\Support\PublicUiText::all($lang);
@endphp
@section('title', ($article?->meta_title ?: $article?->title ?? $ui['article_not_found']) . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))
@section('meta_description', $article?->meta_description ?: $article?->excerpt ?? '')
@section('robots', $article?->robots ?? 'index,follow')
@section('og_type', 'article')
@if($article?->meta_image || $article?->cover_image) @section('meta_image', asset(ltrim($article->meta_image ?: $article->cover_image, '/'))) @endif
@push('styles')
    <style>
        .article-page {
            padding: 46px 0 60px;
            background: #f4f7fb
        }

        .article-detail {
            background: #fff;
            border: 1px solid #dbe4ee;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 22px 70px rgba(7, 23, 40, .08)
        }

        .article-cover {
            margin: 0;
            background: #edf3f8;
            border-bottom: 1px solid #dbe4ee;
            overflow: hidden
        }

        .article-cover img {
            display: block;
            width: 100%;
            height: clamp(190px, 24vw, 300px);
            object-fit: cover
        }

        .article-detail-body {
            padding: 34px
        }

        .article-detail-body h1 {
            font-size: 42px;
            margin: 0 0 14px;
            color: #071728
        }

        .article-meta {
            color: #64748b;
            font-weight: 800;
            margin-bottom: 20px
        }

        .article-text {
            font-size: 17px;
            line-height: 1.9;
            color: #10263a
        }

        .article-text img {
            max-width: 100%;
            height: auto;
            border-radius: 16px;
            margin: 18px 0
        }

        @media(max-width:640px) {
            .article-page {
                padding: 28px 0 44px
            }

            .article-cover img {
                height: 190px
            }

            .article-detail-body {
                padding: 24px
            }

            .article-detail-body h1 {
                font-size: 30px
            }
        }
    </style>
@endpush
@section('content')
@include('public.partials.composition')
@endsection
