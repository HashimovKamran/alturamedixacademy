@php
    $renderer = app(\App\PageBuilder\Rendering\DocumentRenderer::class);
    $document = $revision->document;
    $theme = $theme_settings ?? [];
    $zones = [
        $document['layout']['header'] ?? ['sections' => [], 'order' => []],
        ['sections' => $document['sections'] ?? [], 'order' => $document['order'] ?? []],
        $document['layout']['footer'] ?? ['sections' => [], 'order' => []],
    ];
@endphp
<style>:root{ {{ $renderer->themeCss($theme) }} }</style>
@foreach ($zones as $zone)
    @foreach (($zone['order'] ?? []) as $sectionId)
        @php($section = $zone['sections'][$sectionId] ?? null)
        @if (is_array($section) && !($section['disabled'] ?? false))
            @include($renderer->sectionView((string) ($section['type'] ?? '')), ['section' => $section, 'renderer' => $renderer])
        @endif
    @endforeach
@endforeach

