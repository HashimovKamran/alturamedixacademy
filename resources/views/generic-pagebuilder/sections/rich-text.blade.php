@php($settings = $section['settings'] ?? [])
<section class="pb-preview-section">
    <div class="pb-preview-prose {{ $settings['container'] ?? 'default' }}">
        @if (!empty($settings['title']))<h2>{{ $settings['title'] }}</h2>@endif
        <div>{!! $renderer->rich($settings['content'] ?? '') !!}</div>
    </div>
</section>

