@pbSchema(['name' => 'site_header.blade'])
@php
    $enabled = fn(string $key) => \App\Support\Cms\NativeBlockOptions::enabled($content, $key);
    $headerLogo = trim((string)$block->image_path) ?: $logoImage;
@endphp
<header class="site-header" id="siteHeader" data-block-uuid="{{ $block->block_uuid }}">
    <div class="header-top"><div class="container header-top-inner">
        @if($enabled('show_brand'))<a href="{{ $legacyUrl('/') }}" class="brand"><span class="brand-logo {{ $headerLogo!==''?'has-image':'' }}">@if($headerLogo!=='')<img src="{{ $publicFile($headerLogo) }}" alt="{{ $siteName }}">@else<i class="fa-solid fa-shield-heart"></i>@endif</span><span class="brand-text"><strong data-entity="setting" data-entity-id="site_name" data-entity-field="setting_value">{{ $siteName }}</strong><small data-entity="setting" data-entity-id="site_slogan" data-entity-field="setting_value">{{ $siteSlogan }}</small></span></a>@endif
        <div class="header-tools">
            @if($enabled('show_languages'))<form class="language-switch" aria-label="Dil seçimi"><label for="language_select">Dil seçimi</label><select id="language_select" name="language" onchange="if(this.value){window.location.href=this.value}">@foreach($languages as $language)<option value="{{ app(\App\Services\Site\SeoService::class)->alternate(request(),$language->code) }}" @selected($language->code===$lang)>{{ strtoupper($language->code) }}</option>@endforeach</select></form>@endif
            @if($enabled('show_social'))<div class="social-links"><a href="{{ $siteSettings['social_instagram']??'#' }}" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a><a href="{{ $siteSettings['social_linkedin']??'#' }}" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><a href="{{ $siteSettings['social_youtube']??'#' }}" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a></div>@endif
        </div>
    </div></div>
    <div class="header-nav"><div class="container nav-inner">
        @if($enabled('show_navigation'))<nav class="main-nav" id="mainNav">@foreach($menus as $menu) @php
            $menuKey = \App\Support\CleanUrl::activeKey((string)$menu->url);
            $isActive = $activePage !== '' && $activePage === $menuKey;
            $isAbout = $menuKey === 'about';
        @endphp
        @if($menu->children->isNotEmpty())
            <div class="nav-item nav-has-submenu {{ $isActive?'active':'' }}" data-about-menu><button type="button" class="nav-link {{ $isActive?'active':'' }}" data-about-submenu-toggle aria-expanded="false"><span data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</span> <i class="fa-solid fa-chevron-down"></i></button><div class="about-submenu" data-about-submenu>@foreach($menu->children as $child)<a href="{{ $legacyUrl((string)$child->url) }}" target="{{ $child->target?:'_self' }}" data-entity="menu" data-entity-id="{{ $child->id }}" data-entity-field="title">{{ $child->title }}</a>@endforeach</div></div>
        @elseif($isAbout)
            <div class="nav-item nav-has-submenu {{ $isActive?'active':'' }}" data-about-menu><button type="button" class="nav-link {{ $isActive?'active':'' }}" data-about-submenu-toggle aria-expanded="false"><span data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</span> <i class="fa-solid fa-chevron-down"></i></button><div class="about-submenu" data-about-submenu>@foreach($aboutSubsections as $section)<a class="{{ $isActive&&$activeAboutSection===$section['key']?'active':'' }}" href="{{ $legacyUrl('/about?section='.$section['key']) }}">{{ $section['title'] }}</a>@endforeach</div></div>
        @else
            <a class="{{ $isActive?'active':'' }}" href="{{ $legacyUrl((string)$menu->url) }}" target="{{ $menu->target?:'_self' }}" data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</a>
        @endif @endforeach</nav>@endif
        <div class="nav-actions" id="navActions">
            @if($enabled('show_auth'))@if($currentUser)<a href="{{ route('profile',['lang'=>$lang]) }}" class="btn btn-primary">{{ $currentUser->full_name }}</a><a href="{{ route('site.logout') }}" class="btn btn-outline">{{ $siteSettings['auth_logout']??'Çıxış' }}</a>@else<button type="button" class="btn btn-primary" data-auth-open="register">{{ $siteSettings['btn_register']??$ui['register'] }}</button><button type="button" class="btn btn-outline" data-auth-open="login">{{ $siteSettings['btn_login']??'Daxil ol' }}</button>@endif @endif
            @if($enabled('show_search'))<button class="search-btn" type="button" aria-label="{{ $ui['search'] }}" aria-controls="siteSearch" aria-expanded="false" data-site-search-open><i class="fa-solid fa-magnifying-glass"></i></button>@endif
            @if($enabled('show_navigation'))<button class="mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="Menu" aria-controls="mainNav" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>@endif
        </div>
    </div></div>
</header>
