@php($settings = $section['settings'] ?? [])
<footer class="pb-preview-section dark" style="padding:56px 7vw 28px">
    <div class="pb-preview-content">
        @if (!empty($section['order']))
            <div class="pb-preview-grid c{{ min(4, max(2, count($section['order']))) }}" style="margin-top:0">
                @include('generic-pagebuilder.partials.blocks', ['node' => $section, 'renderer' => $renderer])
            </div>
        @endif
        <div style="margin-top:36px;padding-top:20px;border-top:1px solid rgba(255,255,255,.18);display:flex;flex-wrap:wrap;justify-content:space-between;gap:16px;font-size:13px;opacity:.82">
            <span>{{ $settings['copyright_text'] ?? '' }}</span>
            <span style="display:flex;gap:14px">
                @if (!empty($settings['privacy_url']))<a href="{{ $settings['privacy_url'] }}">Privacy</a>@endif
                @if (!empty($settings['terms_url']))<a href="{{ $settings['terms_url'] }}">Terms</a>@endif
                @if (!empty($settings['facebook_url']))<a href="{{ $settings['facebook_url'] }}" rel="noopener" target="_blank">Facebook</a>@endif
                @if (!empty($settings['instagram_url']))<a href="{{ $settings['instagram_url'] }}" rel="noopener" target="_blank">Instagram</a>@endif
                @if (!empty($settings['twitter_url']))<a href="{{ $settings['twitter_url'] }}" rel="noopener" target="_blank">X</a>@endif
            </span>
        </div>
    </div>
</footer>

