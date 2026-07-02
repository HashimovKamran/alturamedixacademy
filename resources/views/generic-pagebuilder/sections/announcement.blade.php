@php($settings = $section['settings'] ?? [])
<section class="pb-preview-section" style="padding:10px 7vw;text-align:center;background:{{ $settings['background'] ?? '#111827' }};color:{{ $settings['color'] ?? '#ffffff' }}">
    <span>{{ $settings['text'] ?? '' }}</span>
    @if (!empty($settings['link_url']))
        <a style="margin-left:12px;font-weight:800" href="{{ $settings['link_url'] }}">{{ $settings['link_text'] ?? '' }}</a>
    @endif
</section>

