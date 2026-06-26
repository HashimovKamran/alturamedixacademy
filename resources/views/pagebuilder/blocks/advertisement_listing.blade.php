@pbSchema(['name' => 'advertisement_listing.blade'])
@php
    $position = $content['position'] ?? 'bottom';
    $items=\App\Models\Advertisement::query()->forLanguage($lang)->active()->where('position_key',$position)->orderBy('sort_order')->limit(max(1,(int)($content['limit']??4)))->get();
@endphp
@if($position === 'bottom')<div class="container bottom-ads-row">@endif
@foreach($items as $item)
<a href="{{ $item->url ?: '#' }}" class="ad-box {{ $position === 'bottom' ? 'bottom-ad' : '' }}"><div><span>{{ $siteSettings['ad_label'] ?? 'Reklam' }}</span><strong data-entity="advertisement" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title ?: ($siteSettings['ad_default'] ?? '') }}</strong></div>@if($item->image_path)<img src="{{ asset(ltrim($item->image_path,'/')) }}" alt="{{ $item->title }}">@else<i class="fa-regular fa-image"></i>@endif</a>
@endforeach
@if($position === 'bottom')</div>@endif
