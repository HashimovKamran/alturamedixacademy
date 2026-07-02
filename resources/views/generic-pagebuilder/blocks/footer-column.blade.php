@php($settings = $block['settings'] ?? [])
<div>
    <h3 style="margin:0 0 14px;font-size:14px">{{ $settings['title'] ?? '' }}</h3>
    <div style="display:grid;gap:10px">
        @include('generic-pagebuilder.partials.blocks', ['node' => $block, 'renderer' => $renderer])
    </div>
</div>

