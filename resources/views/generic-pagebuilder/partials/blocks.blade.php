@foreach (($node['order'] ?? []) as $blockId)
    @php($block = $node['blocks'][$blockId] ?? null)
    @if (is_array($block) && !($block['disabled'] ?? false))
        @include($renderer->blockView((string) ($block['type'] ?? '')), ['block' => $block, 'renderer' => $renderer])
    @endif
@endforeach

