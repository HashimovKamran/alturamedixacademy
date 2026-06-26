@pbSchema(['name' => 'map_embed.blade'])
@php
    $map = \App\Support\Cms\SafeUrl::clean($content['embed_url'] ?? '');
    $host = strtolower((string) parse_url($map, PHP_URL_HOST));
@endphp
@if($map && (str_contains($host,'google.com')||str_contains($host,'openstreetmap.org')))<div class="container contact-map-container"><section class="aa-home-map-section aa-contact-map-section"><div class="aa-home-map-card"><div class="aa-home-map-head"><div>@if($content['title']??false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif @if(!empty($siteSettings['home_map_subtitle']))<p>{{ $siteSettings['home_map_subtitle'] }}</p>@endif</div><span class="aa-home-map-icon"><i class="fa-solid fa-map-location-dot"></i></span></div><div class="aa-home-map-frame"><iframe src="{{ $map }}" title="{{ $content['title']??'Map' }}" loading="lazy"></iframe></div></div></section></div>@endif
