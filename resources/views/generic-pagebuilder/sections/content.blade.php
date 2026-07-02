@php($settings = $section['settings'] ?? [])
<section class="pb-preview-section" style="background:{{ $settings['background'] ?? '#ffffff' }};padding:{{ ($settings['padding'] ?? 'large') === 'small' ? '28px 7vw' : (($settings['padding'] ?? 'large') === 'medium' ? '44px 7vw' : '64px 7vw') }}">
    <div class="pb-preview-content">
        @include('generic-pagebuilder.partials.blocks', ['node' => $section, 'renderer' => $renderer])
    </div>
</section>

