@pbSchema(['name' => 'partner_listing.blade'])
@php
    $items=\App\Models\Partner::query()->forLanguage($lang)->active()->orderBy('sort_order')->limit(max(1,(int)($content['limit']??20)))->get();
    $repeat=$items->count()<=4?4:2;
@endphp
@if($items->isNotEmpty())
<section class="content-section"><div class="container"><section class="aa-partners-section">
    <div class="aa-partners-head">@if($content['title'] ?? false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif @if($content['subtitle'] ?? false)<p data-inline-field="subtitle">{{ $content['subtitle'] }}</p>@endif</div>
    <div class="aa-partners-shell"><div class="aa-partners-marquee"><div class="aa-partners-track" style="--aa-partners-duration:{{ max(28,$items->count()*$repeat*4) }}s">
        @for($copy=0;$copy<$repeat;$copy++) @foreach($items as $item)<a href="{{ $item->url ?: '#' }}" class="aa-partner-logo-link" @if($item->url) target="_blank" rel="noopener noreferrer" @else onclick="return false" @endif title="{{ $item->title }}">@if($item->logo_path)<img src="{{ asset(ltrim($item->logo_path,'/')) }}" alt="{{ $item->title }}">@else<span class="aa-partner-text" data-entity="partner" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</span>@endif</a>@endforeach @endfor
    </div></div></div>
</section></div></section>
@endif
