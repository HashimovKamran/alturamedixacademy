@php($settings = $block['settings'] ?? [])
<article class="pb-preview-card">
    @if (!empty($settings['icon']))<p class="pb-preview-eyebrow">{{ $settings['icon'] }}</p>@endif
    <h3>{{ $settings['title'] ?? '' }}</h3>
    @if (!empty($settings['description']))<p>{{ $settings['description'] }}</p>@endif
    @if (!empty($settings['url']))<p><a href="{{ $settings['url'] }}" style="font-weight:800">Learn more</a></p>@endif
</article>

