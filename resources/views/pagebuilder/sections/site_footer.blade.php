@pbSchema(['name' => 'site_footer.blade'])
@php
    $enabled = fn(string $key) => \App\Support\Cms\NativeBlockOptions::enabled($content, $key);
    $footerLogo = trim((string)$block->image_path) ?: $logoImage;
    $settings = array_merge($settings, $siteSettings);
@endphp
<footer class="site-footer" data-block-uuid="{{ $block->block_uuid }}">
    <div class="container footer-grid">
        @if($enabled('show_about'))<div class="footer-about"><a href="{{ $legacyUrl('/') }}" class="brand footer-brand"><span class="brand-logo {{ $footerLogo!==''?'has-image':'' }}">@if($footerLogo!=='')<img src="{{ $publicFile($footerLogo) }}" alt="{{ $siteName }}">@else<i class="fa-solid fa-shield-heart"></i>@endif</span><span class="brand-text"><strong data-entity="setting" data-entity-id="site_name" data-entity-field="setting_value">{{ $siteName }}</strong><small data-entity="setting" data-entity-id="site_slogan" data-entity-field="setting_value">{{ $siteSlogan }}</small></span></a><p data-entity="setting" data-entity-id="footer_about" data-entity-field="setting_value">{{ $settings['footer_about']??'' }}</p><div class="footer-social"><a href="{{ $settings['social_instagram']??'#' }}"><i class="fa-brands fa-instagram"></i></a><a href="{{ $settings['social_linkedin']??'#' }}"><i class="fa-brands fa-linkedin-in"></i></a><a href="{{ $settings['social_youtube']??'#' }}"><i class="fa-brands fa-youtube"></i></a></div></div>@endif
        @if($enabled('show_links'))<div><h4>{{ $settings['footer_links']??'Sürətli keçidlər' }}</h4>@foreach($menus as $menu)<a href="{{ $legacyUrl((string)$menu->url) }}">{{ $menu->title }}</a>@endforeach</div>@endif
        @if($enabled('show_contact'))<div><h4>{{ $settings['footer_contact']??$ui['footer_contact'] }}</h4><p><i class="fa-solid fa-phone"></i> {{ $settings['contact_phone']??'' }}</p><p><i class="fa-solid fa-envelope"></i> {{ $settings['contact_email']??'' }}</p><p><i class="fa-solid fa-location-dot"></i> {{ $settings['contact_address']??'' }}</p></div>@endif
        @if($enabled('show_newsletter'))<div><h4>{{ $settings['footer_newsletter']??'Bülleten' }}</h4><p>{{ $settings['newsletter_text']??'' }}</p><form class="newsletter-form"><input type="email" placeholder="{{ $settings['newsletter_placeholder']??'E-mail' }}"><button type="button"><i class="fa-solid fa-arrow-right"></i></button></form></div>@endif
    </div>
    <div class="footer-bottom"><div class="container"><span data-inline-field="copyright_text" data-inline-type="text">{{ \App\Support\Cms\NativeBlockOptions::text($content,'copyright_text','© '.date('Y').' '.$siteName.'. '.$ui['all_rights']) }}</span><span><a href="#">{{ $ui['privacy_policy'] }}</a><a href="#">{{ $ui['terms'] }}</a></span></div></div>
</footer>
