@php($settings = $section['settings'] ?? [])
<section class="pb-preview-section">
    <div class="pb-preview-prose">
        @if (!empty($settings['eyebrow']))<p class="pb-preview-eyebrow">{{ $settings['eyebrow'] }}</p>@endif
        @if (!empty($settings['title']))<h2>{{ $settings['title'] }}</h2>@endif
        @if (!empty($settings['description']))<p>{{ $settings['description'] }}</p>@endif
    </div>
    <div class="pb-preview-grid c{{ $settings['columns'] ?? '3' }}">
        @include('generic-pagebuilder.partials.blocks', ['node' => $section, 'renderer' => $renderer])
    </div>
</section>

