@pbSchema(['name' => 'article_listing.blade'])
@php
    $limit = max(1, min(3, (int) ($content['limit'] ?? 3)));
    $items = \App\Models\Article::query()->with('category')->forLanguage($lang)->active()->latest('published_at')->latest('id')->limit($limit)->get();
    $categories = \App\Models\ArticleCategory::query()->forLanguage($lang)->active()->where('is_featured', true)->orderBy('sort_order')->orderBy('id')->limit(6)->get();
    $allView = $siteSettings['all_view'] ?? ($ui['view_all'] ?? 'Hamısına bax');
    $articleUrl = fn ($article) => \App\Support\CleanUrl::to('article?slug='.urlencode($article->slug), $lang);
@endphp
@if($block->page_key === 'index')
<section class="aa-article-showcase">
    <div class="aa-section-heading"><h2 data-inline-field="title">{{ $content['title'] ?: ($siteSettings['section_articles'] ?? 'Akademik yazılar') }}</h2><a href="{{ \App\Support\CleanUrl::to('articles', $lang) }}">{{ $allView }} <i class="fa-solid fa-arrow-right"></i></a></div>

    @if($categories->isNotEmpty())
        <div class="aa-category-row">
            @foreach($categories as $category)
                <a class="aa-category-card" href="{{ \App\Support\CleanUrl::to('articles?category='.urlencode($category->slug), $lang) }}">
                    <span class="aa-category-image">
                        @if($category->image_path)<img src="{{ asset(ltrim($category->image_path, '/')) }}" alt="{{ $category->title }}">@else<i class="{{ $category->icon_class ?: 'fa-solid fa-book-medical' }}"></i>@endif
                    </span>
                    <strong data-entity="category" data-entity-id="{{ $category->id }}" data-entity-field="title">{{ $category->title }}</strong>
                </a>
            @endforeach
            <a href="{{ \App\Support\CleanUrl::to('articles', $lang) }}" class="aa-category-more" aria-label="{{ $allView }}"><i class="fa-solid fa-arrow-right"></i></a>
        </div>
    @endif

    <div class="aa-article-grid">
        @forelse($items as $item)
            <article class="aa-article-card">
                <a href="{{ $articleUrl($item) }}" class="aa-article-image">
                    @if($item->cover_image)<img src="{{ asset(ltrim($item->cover_image, '/')) }}" alt="{{ $item->title }}">@else<div class="aa-article-placeholder"><i class="fa-regular fa-image"></i></div>@endif
                </a>
                <div class="aa-article-body">
                    <time datetime="{{ optional($item->published_at ?: $item->created_at)->format('Y-m-d') }}">{{ optional($item->published_at ?: $item->created_at)->format('d M Y') }}</time>
                    <h3><a href="{{ $articleUrl($item) }}" data-entity="article" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</a></h3>
                    <a href="{{ $articleUrl($item) }}" class="aa-article-read">{{ $item->category?->title ?: ($ui['read_more'] ?? 'Davamını oxu') }} <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </article>
        @empty
            <div class="aa-empty-state">{{ $ui['no_articles'] }}</div>
        @endforelse
    </div>
</section>
@else
    @if($content['title'] ?? false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif
    <div class="pb-cards">@foreach($items as $item)<a class="pb-card" href="{{ $articleUrl($item) }}">@if($item->cover_image)<img src="{{ asset(ltrim($item->cover_image, '/')) }}" alt="{{ $item->title }}">@endif<strong data-entity="article" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</strong></a>@endforeach</div>
@endif