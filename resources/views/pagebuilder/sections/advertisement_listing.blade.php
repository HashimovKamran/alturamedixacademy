@pbSchema(['name' => 'advertisement_listing.blade'])
@php
    $position = $content['position'] ?? 'bottom';
    $items = \App\Models\Advertisement::query()->forLanguage($lang)->active()->where('position_key', $position)->orderBy('sort_order')->limit(max(1, (int) ($content['limit'] ?? 4)))->get();
@endphp
@if($position === 'sidebar')
    <section class="aa-sidebar-ads">
        @forelse($items as $item)
            <a href="{{ \App\Support\Cms\SafeUrl::clean($item->url ?: '#') }}" class="aa-sidebar-ad">
                <span><small>{{ $siteSettings['ad_label'] ?? 'Reklam' }}</small><strong data-entity="advertisement" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title ?: ($siteSettings['ad_default'] ?? 'Reklam sahəsi') }}</strong></span>
                @if($item->image_path)<img src="{{ asset(ltrim($item->image_path, '/')) }}" alt="{{ $item->title }}">@else<i class="fa-regular fa-image"></i>@endif
            </a>
        @empty
            <div class="aa-sidebar-ad aa-sidebar-ad-empty"><span><small>{{ $siteSettings['ad_label'] ?? 'Reklam' }}</small><strong>{{ $siteSettings['ad_default'] ?? 'Reklam sahəsi' }}</strong></span><i class="fa-regular fa-image"></i></div>
        @endforelse
    </section>
@else
    <div class="container bottom-ads-row">@foreach($items as $item)<a href="{{ \App\Support\Cms\SafeUrl::clean($item->url ?: '#') }}" class="ad-box bottom-ad"><div><span>{{ $siteSettings['ad_label'] ?? 'Reklam' }}</span><strong data-entity="advertisement" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title ?: ($siteSettings['ad_default'] ?? '') }}</strong></div>@if($item->image_path)<img src="{{ asset(ltrim($item->image_path, '/')) }}" alt="{{ $item->title }}">@else<i class="fa-regular fa-image"></i>@endif</a>@endforeach</div>
@endif