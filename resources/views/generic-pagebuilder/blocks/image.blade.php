@php($settings = $block['settings'] ?? [])
@php($image = $renderer->assetUrl($settings['asset_id'] ?? null))
@if ($image)
    <img class="pb-preview-img radius-{{ $settings['radius'] ?? 'medium' }}" src="{{ $image }}" alt="{{ $settings['alt'] ?: $renderer->assetAlt($settings['asset_id'] ?? null) }}">
@endif

