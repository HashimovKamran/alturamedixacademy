@pbSchema(['name' => 'advertisement_listing.blade'])
@php
    $position = $content['position'] ?? 'bottom';
    $items = \App\Models\Advertisement::query()->forLanguage($lang)->active()->where('position_key', $position)->orderBy('sort_order')->limit(max(1, (int) ($content['limit'] ?? 4)))->get();
@endphp
@if($position === 'sidebar')
    <style>
        body.aa-home-page .aa-sidebar-ads{display:grid;gap:12px}
        body.aa-home-page .aa-sidebar-ad{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:94px;padding:15px 16px;border:1px solid #e6edf5;border-radius:10px;background:#fff;color:#21354e;text-decoration:none}
        body.aa-home-page .aa-sidebar-ad span{display:grid;gap:5px;max-width:165px}body.aa-home-page .aa-sidebar-ad small{color:#8291a5;font-size:9px;font-weight:800}body.aa-home-page .aa-sidebar-ad strong{font-size:11px;line-height:1.42}
        body.aa-home-page .aa-sidebar-ad img{width:64px;height:56px;object-fit:cover;border-radius:8px}body.aa-home-page .aa-sidebar-ad>i{display:grid;place-items:center;width:54px;height:50px;border-radius:8px;background:#f0efff;color:#a3a0e5;font-size:24px}
    </style>
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