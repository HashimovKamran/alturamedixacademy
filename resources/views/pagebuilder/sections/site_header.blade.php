@pbSchema(['name' => 'site_header.blade'])
@php
    $enabled = fn(string $key) => \App\Support\Cms\NativeBlockOptions::enabled($content, $key);
    // Brand settings are the single source of truth. The builder image is retained only as a legacy fallback.
    $settingsLogo = trim((string) ($siteSettings['header_logo_image'] ?? '')) ?: trim((string) ($logoImage ?? ''));
    $headerLogo = $settingsLogo !== '' ? $settingsLogo : trim((string) $block->image_path);
@endphp
<header class="site-header aa-site-header" id="siteHeader" data-block-uuid="{{ $block->block_uuid }}">
    <div class="container aa-header-inner">
        @if($enabled('show_brand'))
            <a href="{{ $legacyUrl('/') }}" class="brand aa-brand">
                <span class="brand-logo {{ $headerLogo !== '' ? 'has-image' : '' }}">
                    @if($headerLogo !== '')<img src="{{ $publicFile($headerLogo) }}" alt="{{ $siteName }}">@else<i class="fa-solid fa-shield-heart"></i>@endif
                </span>
                <span class="brand-text"><strong data-entity="setting" data-entity-id="site_name" data-entity-field="setting_value">{{ $siteName }}</strong><small data-entity="setting" data-entity-id="site_slogan" data-entity-field="setting_value">{{ $siteSlogan }}</small></span>
            </a>
        @endif

        @if($enabled('show_navigation'))
            <nav class="main-nav aa-main-nav" id="mainNav">
                @foreach($menus as $menu)
                    @php
                        $menuKey = \App\Support\CleanUrl::activeKey((string) $menu->url);
                        $isActive = $activePage !== '' && $activePage === $menuKey;
                        $isAbout = $menuKey === 'about';
                    @endphp
                    @if($menu->children->isNotEmpty())
                        <div class="nav-item nav-has-submenu {{ $isActive ? 'active' : '' }}">
                            <button type="button" class="nav-link {{ $isActive ? 'active' : '' }}" data-about-submenu-toggle aria-expanded="false"><span data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</span><i class="fa-solid fa-chevron-down"></i></button>
                            <div class="about-submenu" data-about-submenu>@foreach($menu->children as $child)<a href="{{ $legacyUrl((string) $child->url) }}" target="{{ $child->target ?: '_self' }}" data-entity="menu" data-entity-id="{{ $child->id }}" data-entity-field="title">{{ $child->title }}</a>@endforeach</div>
                        </div>
                    @elseif($isAbout)
                        <div class="nav-item nav-has-submenu {{ $isActive ? 'active' : '' }}" data-about-menu>
                            <button type="button" class="nav-link {{ $isActive ? 'active' : '' }}" data-about-submenu-toggle aria-expanded="false"><span data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</span><i class="fa-solid fa-chevron-down"></i></button>
                            <div class="about-submenu" data-about-submenu>@foreach($aboutSubsections as $section)<a class="{{ $isActive && $activeAboutSection === $section['key'] ? 'active' : '' }}" href="{{ $legacyUrl('/about?section='.$section['key']) }}">{{ $section['title'] }}</a>@endforeach</div>
                        </div>
                    @else
                        <a class="{{ $isActive ? 'active' : '' }}" href="{{ $legacyUrl((string) $menu->url) }}" target="{{ $menu->target ?: '_self' }}" data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</a>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="nav-actions aa-nav-actions" id="navActions">
            @if($enabled('show_auth'))
                @if($currentUser)
                    <a href="{{ route('profile', ['lang' => $lang]) }}" class="aa-user-link">{{ $currentUser->full_name }}</a>
                    <a href="{{ route('site.logout') }}" class="aa-login-button">{{ $siteSettings['auth_logout'] ?? 'Çıxış' }}</a>
                @else
                    <button type="button" class="aa-login-button" data-auth-open="login">{{ $siteSettings['btn_login'] ?? 'Daxil ol' }}</button>
                @endif
            @endif
            @if($enabled('show_search'))<button class="aa-header-icon search-btn" type="button" aria-label="{{ $ui['search'] }}" data-site-search-open><i class="fa-solid fa-magnifying-glass"></i></button>@endif
            @if($enabled('show_languages'))
                <div class="aa-language-switch"><i class="fa-solid fa-globe"></i>@foreach($languages->filter(fn($language) => $language->code !== $lang)->take(1) as $language)<a href="{{ app(\App\Services\Site\SeoService::class)->alternate(request(), $language->code) }}">{{ strtoupper($language->code) }}</a>@endforeach</div>
            @endif
            @if($enabled('show_navigation'))<button class="mobile-menu-btn aa-mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="Menu" aria-controls="mainNav" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>@endif
        </div>
    </div>
</header>
