@extends('layouts.public')

@section('title', ($settings['section_academic'] ?? 'Akademik yazılar') . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))
@section('meta_description', $settings['site_description'] ?? '')

@php
    $pageTitle = $settings['section_academic'] ?? 'Akademik yazılar';
    $articlesUrl = \App\Support\CleanUrl::to('articles', $lang);
    $homeUrl = \App\Support\CleanUrl::to('/', $lang);
    $articleUrl = fn ($article) => \App\Support\CleanUrl::to('article?slug='.urlencode($article->slug), $lang);
    $heroArticle = $articles->getCollection()->first(fn ($article) => filled($article->cover_image)) ?: $popularArticles->first(fn ($article) => filled($article->cover_image));
    $heroImage = $heroArticle?->cover_image ? asset(ltrim($heroArticle->cover_image, '/')) : '';
    $dateFor = fn ($article) => $article->published_at ?: $article->created_at;
    $excerptFor = fn ($article) => \Illuminate\Support\Str::limit(trim(strip_tags((string) ($article->excerpt ?: $article->body))), 190);
    $categoryIcon = fn ($category) => $category->icon_class ?: 'fa-solid fa-book-medical';
    $categoryUrl = fn ($slug = '') => $articlesUrl.($slug !== '' ? '?category='.urlencode($slug) : '');
    $paginationStart = max(1, $articles->currentPage() - 2);
    $paginationEnd = min($articles->lastPage(), $articles->currentPage() + 2);
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/articles-page.css') }}">
@endpush

@section('content')
<main class="articles-page">
    <section class="articles-hero" @if($heroImage) style="--articles-hero-image:url('{{ $heroImage }}')" @endif>
        <div class="container articles-hero-inner">
            <nav class="articles-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ $homeUrl }}">Ana səhifə</a><i class="fa-solid fa-chevron-right"></i><span>{{ $pageTitle }}</span>
            </nav>
            <div class="articles-hero-copy">
                <h1>{{ $pageTitle }}</h1>
                <p>{{ $settings['articles_intro'] ?? 'Təcili və kritik tibb sahəsində ən son tədqiqatlar, elmi məqalələr, icmallar və dəlillərə əsaslanan biliklər.' }}</p>
            </div>
            <form class="articles-filter-form" method="get" action="{{ $articlesUrl }}">
                <label class="articles-search-field">
                    <span class="sr-only">Məqalə axtarın</span>
                    <input type="search" name="q" value="{{ $searchQuery }}" placeholder="Məqalə axtarın...">
                    @if($selectedCategory)<input type="hidden" name="category" value="{{ $selectedCategory }}">@endif
                    <input type="hidden" name="sort" value="{{ $selectedSort }}">
                    <button type="submit" aria-label="Axtar"><i class="fa-solid fa-magnifying-glass"></i></button>
                </label>
                <label class="articles-category-select">
                    <span class="sr-only">Kateqoriya seçin</span>
                    <select name="category" onchange="this.form.submit()">
                        <option value="">Kateqoriya</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected($selectedCategory === $category->slug)>{{ $category->title }}</option>
                        @endforeach
                    </select>
                </label>
            </form>
        </div>
    </section>

    <section class="articles-archive">
        <div class="container articles-layout">
            <div class="articles-main-column">
                <div class="articles-toolbar">
                    <p>Ümumi məqalə sayı: <strong>{{ $articles->total() }}</strong></p>
                    <form method="get" action="{{ $articlesUrl }}" class="articles-sort-form">
                        @if($searchQuery)<input type="hidden" name="q" value="{{ $searchQuery }}">@endif
                        @if($selectedCategory)<input type="hidden" name="category" value="{{ $selectedCategory }}">@endif
                        <select name="sort" onchange="this.form.submit()" aria-label="Sıralama">
                            <option value="latest" @selected($selectedSort === 'latest')>Ən yenilər</option>
                            <option value="oldest" @selected($selectedSort === 'oldest')>Ən köhnələr</option>
                            <option value="title" @selected($selectedSort === 'title')>A-dan Z-yə</option>
                        </select>
                    </form>
                </div>

                <div class="articles-listing">
                    @forelse($articles as $article)
                        <article class="archive-article-card">
                            <a href="{{ $articleUrl($article) }}" class="archive-article-cover">
                                @if($article->cover_image)
                                    <img src="{{ asset(ltrim($article->cover_image, '/')) }}" alt="{{ $article->title }}">
                                @else
                                    <span><i class="fa-regular fa-image"></i></span>
                                @endif
                            </a>
                            <div class="archive-article-copy">
                                <span class="archive-article-category">{{ $article->category?->title ?: 'Məqalə' }}</span>
                                <h2><a href="{{ $articleUrl($article) }}">{{ $article->title }}</a></h2>
                                <div class="archive-article-meta">
                                    <span><i class="fa-regular fa-calendar"></i>{{ optional($dateFor($article))->format('d M Y') }}</span>
                                    @if($article->author_name)<span><i class="fa-regular fa-user"></i>{{ $article->author_name }}</span>@endif
                                </div>
                                @if($excerptFor($article))<p>{{ $excerptFor($article) }}</p>@endif
                                <a href="{{ $articleUrl($article) }}" class="archive-read-more">Davamını oxu <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </article>
                    @empty
                        <div class="archive-empty"><i class="fa-regular fa-folder-open"></i><strong>Nəticə tapılmadı</strong><span>Axtarış və ya kateqoriya filtrini dəyişərək yenidən yoxlayın.</span><a href="{{ $articlesUrl }}">Bütün məqalələri göstər</a></div>
                    @endforelse
                </div>

                @if($articles->hasPages())
                    <nav class="articles-pagination" aria-label="Səhifələmə">
                        @if($articles->onFirstPage())<span class="is-disabled"><i class="fa-solid fa-chevron-left"></i></span>@else<a href="{{ $articles->previousPageUrl() }}" aria-label="Əvvəlki səhifə"><i class="fa-solid fa-chevron-left"></i></a>@endif
                        @if($paginationStart > 1)<a href="{{ $articles->url(1) }}">1</a>@if($paginationStart > 2)<span class="is-dots">…</span>@endif@endif
                        @for($page = $paginationStart; $page <= $paginationEnd; $page++)
                            <a href="{{ $articles->url($page) }}" class="{{ $page === $articles->currentPage() ? 'is-active' : '' }}">{{ $page }}</a>
                        @endfor
                        @if($paginationEnd < $articles->lastPage())@if($paginationEnd < $articles->lastPage() - 1)<span class="is-dots">…</span>@endif<a href="{{ $articles->url($articles->lastPage()) }}">{{ $articles->lastPage() }}</a>@endif
                        @if($articles->hasMorePages())<a href="{{ $articles->nextPageUrl() }}" aria-label="Növbəti səhifə"><i class="fa-solid fa-chevron-right"></i></a>@else<span class="is-disabled"><i class="fa-solid fa-chevron-right"></i></span>@endif
                    </nav>
                @endif
            </div>

            <aside class="articles-sidebar">
                <section class="articles-side-card categories-side-card">
                    <h2>Kateqoriyalar</h2>
                    <a href="{{ $articlesUrl }}" class="side-category {{ $selectedCategory === '' ? 'is-active' : '' }}"><span><i class="fa-solid fa-layer-group"></i>Bütün məqalələr</span><b>{{ $categories->sum('articles_count') }}</b></a>
                    @foreach($categories as $category)
                        <a href="{{ $categoryUrl($category->slug) }}" class="side-category {{ $selectedCategory === $category->slug ? 'is-active' : '' }}"><span>@if($category->image_path)<img src="{{ asset(ltrim($category->image_path, '/')) }}" alt="">@else<i class="{{ $categoryIcon($category) }}"></i>@endif{{ $category->title }}</span><b>{{ $category->articles_count }}</b></a>
                    @endforeach
                </section>

                <section class="articles-side-card popular-side-card">
                    <h2>Ən son məqalələr</h2>
                    <div class="popular-articles-list">
                        @foreach($popularArticles as $article)
                            <a href="{{ $articleUrl($article) }}" class="popular-article">
                                <span class="popular-article-image">@if($article->cover_image)<img src="{{ asset(ltrim($article->cover_image, '/')) }}" alt="">@else<i class="fa-regular fa-image"></i>@endif</span>
                                <span><strong>{{ $article->title }}</strong><small><i class="fa-regular fa-calendar"></i>{{ optional($dateFor($article))->format('d M Y') }}</small></span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="articles-side-card articles-newsletter">
                    <i class="fa-regular fa-paper-plane articles-newsletter-art" aria-hidden="true"></i>
                    <h2>Yeniliklərdən xəbərdar olun</h2>
                    <p>Yeni məqalə və yeniliklərdən ilk siz xəbərdar olun.</p>
                    <form onsubmit="return false">
                        <input type="email" placeholder="E-poçt ünvanınız" aria-label="E-poçt ünvanınız">
                        <button type="submit">Abunə ol</button>
                    </form>
                </section>
            </aside>
        </div>
    </section>
</main>
@endsection
