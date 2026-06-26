@pbSchema(['name' => 'gallery.blade'])
@if($content['title'] ?? false)<h2>{{ $content['title'] }}</h2>@endif
<div class="pb-gallery">
@if($children->isNotEmpty())
@foreach($children as $child)
@php
    $item = $child['block'];
    $assetId = filter_var($item->asset_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $src = $assetId ? app(\App\AlturaPageBuilder\Rendering\VisualAssetPathResolver::class)->url((int)$assetId) : \App\Support\Cms\SafeUrl::clean($item->image ?? '', '');
@endphp
@if($src)<figure><img src="{{ $src }}" alt="{{ $item->title ?? '' }}" loading="lazy"><figcaption><strong>{{ $item->title ?? '' }}</strong><span>{{ $item->text ?? '' }}</span></figcaption></figure>@endif
@endforeach
@else
@foreach((array)($content['items'] ?? []) as $item)
@php($src = \App\Support\Cms\SafeUrl::clean($item['image'] ?? '', ''))
@if($src)<figure><img src="{{ $src }}" alt="{{ $item['title'] ?? '' }}" loading="lazy"><figcaption><strong>{{ $item['title'] ?? '' }}</strong><span>{{ $item['text'] ?? '' }}</span></figcaption></figure>@endif
@endforeach
@endif
</div>
