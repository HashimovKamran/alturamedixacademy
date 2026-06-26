@php($settings = $section['settings'] ?? [])
@php($variant = $settings['variant'] ?? 'brand')
<section class="pb-preview-section {{ $variant === 'dark' ? 'dark' : '' }}" @if($variant === 'brand') style="background:var(--pb-primary);color:#fff" @elseif($variant === 'muted') style="background:#f3f5f8" @endif>
    <div class="pb-preview-prose">
        <h2>{{ $settings['title'] ?? '' }}</h2>
        @if (!empty($settings['description']))<p>{{ $settings['description'] }}</p>@endif
        @if (!empty($settings['button_text']) && !empty($settings['button_url']))<a class="pb-preview-button" href="{{ $settings['button_url'] }}">{{ $settings['button_text'] }}</a>@endif
    </div>
</section>

