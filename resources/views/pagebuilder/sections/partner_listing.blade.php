@pbSchema(['name' => 'partner_listing.blade'])
@php($items = \App\Models\Partner::query()->forLanguage($lang)->active()->orderBy('sort_order')->limit(max(1, (int) ($content['limit'] ?? 20)))->get())
@if($items->isNotEmpty())
<section class="aa-partner-strip"><div class="container"><div class="aa-partner-grid">
    @foreach($items as $item)
        <a href="{{ $item->url ?: '#' }}" class="aa-partner-item" @if($item->url) target="_blank" rel="noopener noreferrer" @else onclick="return false" @endif title="{{ $item->title }}">
            @if($item->logo_path)<img src="{{ asset(ltrim($item->logo_path, '/')) }}" alt="{{ $item->title }}">@else<span>{{ $item->title }}</span>@endif
        </a>
    @endforeach
</div></div></section>
@endif