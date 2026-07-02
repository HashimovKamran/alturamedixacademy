@pbSchema(['name' => 'site_footer.blade'])
@php
    $enabled = fn(string $key) => \App\Support\Cms\NativeBlockOptions::enabled($content, $key);
    // Keep the public brand logo controlled from Settings. The block image is only a legacy fallback.
    $settingsLogo = trim((string) ($siteSettings['header_logo_image'] ?? '')) ?: trim((string) ($logoImage ?? ''));
    $footerLogo = $settingsLogo !== '' ? $settingsLogo : trim((string) $block->image_path);
    $settings = array_merge($settings, $siteSettings);
    $isFooterHome = ($activePage ?? '') === 'index';
    $footerSocials = [
        ['key' => 'social_facebook', 'icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
        ['key' => 'social_linkedin', 'icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
        ['key' => 'social_youtube', 'icon' => 'fa-brands fa-youtube', 'label' => 'YouTube'],
        ['key' => 'social_instagram', 'icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
    ];
@endphp
<footer class="site-footer" data-block-uuid="{{ $block->block_uuid }}">
    @if($isFooterHome)
        @if($enabled('show_about'))
            <div class="footer-about">
                <a href="{{ $legacyUrl('/') }}" class="brand footer-brand">
                    <span class="brand-logo {{ $footerLogo !== '' ? 'has-image' : '' }}">
                        @if($footerLogo !== '')
                            <img src="{{ $publicFile($footerLogo) }}" alt="{{ $siteName }}">
                        @else
                            <i class="fa-solid fa-shield-heart"></i>
                        @endif
                    </span>
                    <span class="brand-text">
                        <strong data-entity="setting" data-entity-id="site_name" data-entity-field="setting_value">{{ $siteName }}</strong>
                        <small data-entity="setting" data-entity-id="site_slogan" data-entity-field="setting_value">{{ $siteSlogan }}</small>
                    </span>
                </a>
                <p data-entity="setting" data-entity-id="footer_about" data-entity-field="setting_value">{{ $settings['footer_about'] ?? '' }}</p>
                <div class="footer-social">
                    @foreach($footerSocials as $social)
                        @php($socialUrl = trim((string) ($settings[$social['key']] ?? '')))
                        <a href="{{ $socialUrl !== '' ? $socialUrl : '#' }}" aria-label="{{ $social['label'] }}" @if($socialUrl !== '') target="_blank" rel="noopener noreferrer" @endif>
                            <i class="{{ $social['icon'] }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        @if($enabled('show_links'))
            <div class="footer-links footer-column">
                <h4>{{ $settings['footer_links'] ?? 'Sürətli keçidlər' }}</h4>
                @foreach($menus as $menu)
                    <a href="{{ $legacyUrl((string) $menu->url) }}">{{ $menu->title }}</a>
                @endforeach
            </div>
        @endif
        @if($enabled('show_contact'))
            <div class="footer-contact footer-column">
                <h4>{{ $settings['footer_contact'] ?? $ui['footer_contact'] }}</h4>
                <p><i class="fa-solid fa-phone"></i><span>{{ $settings['contact_phone'] ?? '' }}</span></p>
                <p><i class="fa-solid fa-envelope"></i><span>{{ $settings['contact_email'] ?? '' }}</span></p>
                <p><i class="fa-solid fa-location-dot"></i><span>{{ $settings['contact_address'] ?? '' }}</span></p>
            </div>
        @endif
        @if($enabled('show_newsletter'))
            <div class="footer-newsletter footer-column">
                <h4>{{ $settings['footer_newsletter'] ?? 'Bülleten' }}</h4>
                <p>{{ $settings['newsletter_text'] ?? '' }}</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="{{ $settings['newsletter_placeholder'] ?? 'E-mail ünvanınız' }}">
                    <button type="button" aria-label="{{ $settings['footer_newsletter'] ?? 'Bülleten' }}"><i class="fa-solid fa-arrow-right"></i></button>
                </form>
            </div>
        @endif
    <div class="footer-bottom aa-footer-bottom-v2">
        <div class="container">
            <span data-inline-field="copyright_text" data-inline-type="text">{{ \App\Support\Cms\NativeBlockOptions::text($content, 'copyright_text', '© '.date('Y').' '.$siteName.'. '.$ui['all_rights']) }}</span>
            <span><a href="#">{{ $ui['privacy_policy'] }}</a><a href="#">{{ $ui['terms'] }}</a></span>
        </div>
    </div>
    @else
    <div class="container footer-grid">
        @if($enabled('show_about'))
            <div class="footer-about">
                <a href="{{ $legacyUrl('/') }}" class="brand footer-brand">
                    <span class="brand-logo {{ $footerLogo !== '' ? 'has-image' : '' }}">
                        @if($footerLogo !== '')
                            <img src="{{ $publicFile($footerLogo) }}" alt="{{ $siteName }}">
                        @else
                            <i class="fa-solid fa-shield-heart"></i>
                        @endif
                    </span>
                    <span class="brand-text">
                        <strong data-entity="setting" data-entity-id="site_name" data-entity-field="setting_value">{{ $siteName }}</strong>
                        <small data-entity="setting" data-entity-id="site_slogan" data-entity-field="setting_value">{{ $siteSlogan }}</small>
                    </span>
                </a>
                <p data-entity="setting" data-entity-id="footer_about" data-entity-field="setting_value">{{ $settings['footer_about'] ?? '' }}</p>
                <div class="footer-social">
                    @foreach($footerSocials as $social)
                        @php($socialUrl = trim((string) ($settings[$social['key']] ?? '')))
                        <a href="{{ $socialUrl !== '' ? $socialUrl : '#' }}" aria-label="{{ $social['label'] }}" @if($socialUrl !== '') target="_blank" rel="noopener noreferrer" @endif>
                            <i class="{{ $social['icon'] }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        @if($enabled('show_links'))<div><h4>{{ $settings['footer_links']??'Sürətli keçidlər' }}</h4>@foreach($menus as $menu)<a href="{{ $legacyUrl((string)$menu->url) }}">{{ $menu->title }}</a>@endforeach</div>@endif
        @if($enabled('show_contact'))<div><h4>{{ $settings['footer_contact']??$ui['footer_contact'] }}</h4><p><i class="fa-solid fa-phone"></i> {{ $settings['contact_phone']??'' }}</p><p><i class="fa-solid fa-envelope"></i> {{ $settings['contact_email']??'' }}</p><p><i class="fa-solid fa-location-dot"></i> {{ $settings['contact_address']??'' }}</p></div>@endif
        @if($enabled('show_newsletter'))<div><h4>{{ $settings['footer_newsletter']??'Bülleten' }}</h4><p>{{ $settings['newsletter_text']??'' }}</p><form class="newsletter-form"><input type="email" placeholder="{{ $settings['newsletter_placeholder']??'E-mail' }}"><button type="button"><i class="fa-solid fa-arrow-right"></i></button></form></div>@endif
    </div>
    <div class="footer-bottom"><div class="container"><span data-inline-field="copyright_text" data-inline-type="text">{{ \App\Support\Cms\NativeBlockOptions::text($content,'copyright_text','© '.date('Y').' '.$siteName.'. '.$ui['all_rights']) }}</span><span><a href="#">{{ $ui['privacy_policy'] }}</a><a href="#">{{ $ui['terms'] }}</a></span></div></div>
    @endif
</footer>
