@php($settings = $block['settings'] ?? [])
<div class="pb-preview-row columns-{{ $settings['columns'] ?? '2' }}" style="gap:{{ ($settings['gap'] ?? 'medium') === 'small' ? '8px' : (($settings['gap'] ?? 'medium') === 'large' ? '32px' : '16px') }}">
    @include('generic-pagebuilder.partials.blocks', ['node' => $block, 'renderer' => $renderer])
</div>

