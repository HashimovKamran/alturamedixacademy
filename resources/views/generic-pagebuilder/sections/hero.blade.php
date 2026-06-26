@php($settings = $section['settings'] ?? [])
@php($variant = $settings['variant'] ?? 'light')
<section class="pb-preview-section {{ $variant === 'dark' ? 'dark' : '' }}" @if($variant === 'brand') style="background:var(--pb-primary);color:#fff" @endif>
    <div class="pb-preview-hero {{ ($settings['alignment'] ?? 'left') === 'center' ? 'center' : '' }}">
        <div>
            @if (!empty($settings['eyebrow']))<p class="pb-preview-eyebrow">{{ $settings['eyebrow'] }}</p>@endif
            <h1>{{ $settings['title'] ?? '' }}</h1>
            @if (!empty($settings['description']))<p class="pb-preview-copy">{{ $settings['description'] }}</p>@endif
            <div class="pb-preview-actions">
                @if (!empty($settings['primary_text']) && !empty($settings['primary_url']))<a class="pb-preview-button" href="{{ $settings['primary_url'] }}">{{ $settings['primary_text'] }}</a>@endif
                @if (!empty($settings['secondary_text']) && !empty($settings['secondary_url']))<a class="pb-preview-button secondary" href="{{ $settings['secondary_url'] }}">{{ $settings['secondary_text'] }}</a>@endif
            </div>
        </div>
        @php($image = $renderer->assetUrl($settings['image_id'] ?? null))
        @if ($image)<img class="pb-preview-image" src="{{ $image }}" alt="{{ $renderer->assetAlt($settings['image_id'] ?? null, (string) ($settings['title'] ?? '')) }}">@endif
    </div>
</section>

