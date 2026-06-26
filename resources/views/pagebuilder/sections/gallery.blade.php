@pbSchema(['name' => 'gallery.blade'])
@if($content['title']??false)<h2>{{ $content['title'] }}</h2>@endif
<div class="pb-gallery">@foreach($content['items']??[] as $item)
    @php
        $src = \App\Support\Cms\SafeUrl::clean($item['image'] ?? '', '');
    @endphp
    @if($src)<figure><img src="{{ $src }}" alt="{{ $item['title']??'' }}" loading="lazy"><figcaption><strong>{{ $item['title']??'' }}</strong><span>{{ $item['text']??'' }}</span></figcaption></figure>@endif
@endforeach</div>
