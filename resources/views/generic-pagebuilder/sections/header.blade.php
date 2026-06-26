@php($settings = $section['settings'] ?? [])
<header class="pb-preview-section" style="padding:{{ !empty($settings['sticky']) ? '14px 7vw' : '18px 7vw' }};background:#fff;border-bottom:1px solid #e7ebf1;position:{{ !empty($settings['sticky']) ? 'sticky' : 'relative' }};top:0;z-index:10">
    <div class="pb-preview-content" style="display:flex;align-items:center;justify-content:space-between;gap:24px">
        <a href="{{ $settings['logo_url'] ?? '/' }}" style="font-size:20px;font-weight:850;text-decoration:none">{{ $settings['logo_text'] ?? '' }}</a>
        <nav style="display:flex;gap:18px;align-items:center">
            @include('generic-pagebuilder.partials.blocks', ['node' => $section, 'renderer' => $renderer])
        </nav>
        @if (!empty($settings['cta_label']) && !empty($settings['cta_url']))
            <a class="pb-preview-button" href="{{ $settings['cta_url'] }}">{{ $settings['cta_label'] }}</a>
        @endif
    </div>
</header>

