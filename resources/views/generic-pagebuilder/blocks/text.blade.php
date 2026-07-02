@php($settings = $block['settings'] ?? [])
<div class="pb-preview-text {{ $settings['alignment'] ?? 'left' }}">
    {!! $renderer->rich($settings['content'] ?? '') !!}
</div>

