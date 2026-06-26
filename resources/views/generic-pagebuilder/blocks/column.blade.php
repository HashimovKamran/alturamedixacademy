@php($settings = $block['settings'] ?? [])
<div class="pb-preview-column pad-{{ $settings['padding'] ?? 'medium' }}" style="background:{{ $settings['background'] ?? 'transparent' }}">
    @include('generic-pagebuilder.partials.blocks', ['node' => $block, 'renderer' => $renderer])
</div>

