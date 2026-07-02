@pbSchema(['name' => 'partner_listing.blade'])
@php
    $items = \App\Models\Partner::query()
        ->forLanguage($lang)
        ->active()
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();
    // Approximately 44px/sec regardless of partner count, while keeping the complete cycle reasonable.
    $duration = max(28, min(120, (int) ceil($items->count() * 4)));
@endphp
@if($items->isNotEmpty())
<section class="aa-partner-strip">
    <div class="container">
        <div class="aa-partner-marquee {{ $items->count() === 1 ? 'is-static' : '' }}" style="--aa-partner-duration: {{ $duration }}s" role="region" aria-label="{{ $siteSettings['section_partners'] ?? 'Tərəfdaşlar' }}">
            <div class="aa-partner-track">
                <div class="aa-partner-group">
                    @foreach($items as $item)
                        <a href="{{ $item->url ? \App\Support\Cms\SafeUrl::clean($item->url) : '#' }}" class="aa-partner-item" @if($item->url) target="_blank" rel="noopener noreferrer" @else onclick="return false" @endif title="{{ $item->title }}">
                            @if($item->logo_path)<img src="{{ asset(ltrim($item->logo_path, '/')) }}" alt="{{ $item->title }}">@else<span>{{ $item->title }}</span>@endif
                        </a>
                    @endforeach
                </div>
                @if($items->count() > 1)
                    <div class="aa-partner-group" aria-hidden="true">
                        @foreach($items as $item)
                            <a href="#" class="aa-partner-item" tabindex="-1">
                                @if($item->logo_path)<img src="{{ asset(ltrim($item->logo_path, '/')) }}" alt="">@else<span>{{ $item->title }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif