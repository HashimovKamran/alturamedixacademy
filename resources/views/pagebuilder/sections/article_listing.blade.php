@pbSchema(['name' => 'article_listing.blade'])
@php
    $limit = max(1, min(24, (int) ($content['limit'] ?? 6)));
    $items = \App\Models\Article::query()->forLanguage($lang)->active()->latest('published_at')->limit($limit)->get();
    $settings['all_view'] = $siteSettings['all_view'] ?? ($ui['view_all'] ?? 'Hamısına bax');
@endphp
@if($block->page_key==='index')
<div class="section-head">@if($content['title']??false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif<a href="{{ \App\Support\CleanUrl::to('articles',$lang) }}">{{ $settings['all_view']??'Hamısına bax' }} <i class="fa-solid fa-arrow-right"></i></a></div>
<div class="article-grid">@forelse($items as $item)<article class="article-card"><a href="{{ \App\Support\CleanUrl::to('article?slug='.urlencode($item->slug),$lang) }}" class="article-image">@if($item->cover_image)<img src="{{ asset(ltrim($item->cover_image,'/')) }}" alt="{{ $item->title }}">@else<div class="image-placeholder"><i class="fa-regular fa-image"></i></div>@endif</a><div class="article-body"><h3><a href="{{ \App\Support\CleanUrl::to('article?slug='.urlencode($item->slug),$lang) }}" data-entity="article" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</a></h3><div class="article-footer"><span>{{ optional($item->published_at?:$item->created_at)->format('d.m.Y') }}</span><a href="{{ \App\Support\CleanUrl::to('article?slug='.urlencode($item->slug),$lang) }}"><i class="fa-solid fa-arrow-right"></i></a></div></div></article>@empty<div class="empty-box">{{ $ui['no_articles'] }}</div>@endforelse</div>
@else
@if($content['title']??false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif<div class="pb-cards">@foreach($items as $item)<a class="pb-card" href="{{ \App\Support\CleanUrl::to('article?slug='.urlencode($item->slug),$lang) }}">@if($item->cover_image)<img src="{{ asset(ltrim($item->cover_image,'/')) }}" alt="{{ $item->title }}">@endif<strong data-entity="article" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</strong></a>@endforeach</div>
@endif
