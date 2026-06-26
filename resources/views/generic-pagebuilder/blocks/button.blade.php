@php($settings = $block['settings'] ?? [])
<div class="pb-preview-text {{ $settings['alignment'] ?? 'left' }}">
    @if (!empty($settings['text']) && !empty($settings['url']))
        <a class="pb-preview-button {{ ($settings['style'] ?? 'primary') === 'secondary' ? 'secondary' : '' }}" href="{{ $settings['url'] }}">{{ $settings['text'] }}</a>
    @endif
</div>

