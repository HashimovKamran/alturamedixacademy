@pbSchema(['name' => 'site_header.blade'])
@php
    $enabled = fn(string $key) => \App\Support\Cms\NativeBlockOptions::enabled($content, $key);
    // Brand settings are the single source of truth. The builder image is retained only as a legacy fallback.
    $settingsLogo = trim((string) ($siteSettings['header_logo_image'] ?? '')) ?: trim((string) ($logoImage ?? ''));
    $headerLogo = $settingsLogo !== '' ? $settingsLogo : trim((string) $block->image_path);
@endphp
<style data-aa-shared-header>
/* Shared public header shell. It intentionally lives with the shared header view so every public page uses the same layout contract. */
.aa-site-header{width:100%;z-index:1000;color:#fff}
.aa-site-header .aa-header-inner{position:relative;display:flex;align-items:center;gap:clamp(15px,2vw,32px);width:min(1440px,calc(100% - 56px));min-height:82px;margin-inline:auto}
.aa-site-header .aa-brand{display:flex;align-items:center;gap:10px;min-width:max-content;color:#fff;text-decoration:none;flex:0 0 auto}
.aa-site-header .aa-brand .brand-logo{display:grid;place-items:center;width:54px;height:54px;flex:0 0 auto;border:0!important;border-radius:0!important;background:transparent!important;overflow:visible!important}
.aa-site-header .aa-brand .brand-logo img{width:100%;height:100%;object-fit:contain;padding:0!important;filter:none!important}
.aa-site-header .aa-brand .brand-text{display:grid;gap:4px;min-width:0}
.aa-site-header .aa-brand .brand-text strong{display:block;color:#fff;font-size:14px;line-height:1;letter-spacing:.08em;font-weight:900;white-space:nowrap}
.aa-site-header .aa-brand .brand-text small{display:block;margin:0;color:rgba(255,255,255,.72);font-size:8px;line-height:1;letter-spacing:.15em;font-style:normal;font-weight:750;white-space:nowrap}
.aa-site-header .aa-main-nav{display:flex;align-self:stretch;align-items:center;justify-content:center;gap:clamp(17px,1.7vw,29px);min-width:0;flex:1 1 auto}
.aa-site-header .aa-main-nav>a,.aa-site-header .aa-main-nav .nav-link{position:relative;display:inline-flex;align-items:center;gap:6px;min-height:82px;margin:0;padding:0!important;border:0!important;background:transparent!important;color:rgba(255,255,255,.92);font:800 12px/1.2 var(--font-main,"Noto Sans",sans-serif)!important;text-decoration:none;white-space:nowrap;cursor:pointer}
.aa-site-header .aa-main-nav>a:after,.aa-site-header .aa-main-nav .nav-link:after{content:"";position:absolute;right:0;bottom:16px;left:0;height:2px;border-radius:99px;background:#ff741c;opacity:0;transform:scaleX(.5);transition:opacity .18s ease,transform .18s ease}
.aa-site-header .aa-main-nav>a:hover,.aa-site-header .aa-main-nav>a.active,.aa-site-header .aa-main-nav .nav-link:hover,.aa-site-header .aa-main-nav .nav-link.active{color:#fff}
.aa-site-header .aa-main-nav>a:hover:after,.aa-site-header .aa-main-nav>a.active:after,.aa-site-header .aa-main-nav .nav-link:hover:after,.aa-site-header .aa-main-nav .nav-link.active:after{opacity:1;transform:scaleX(1)}
.aa-site-header .nav-has-submenu{position:relative;display:flex;align-self:stretch;align-items:center}.aa-site-header .about-submenu{position:absolute;top:calc(100% - 10px);left:50%;display:none;min-width:190px;padding:8px;border:1px solid rgba(255,255,255,.14);border-radius:12px;background:#06213a;box-shadow:0 20px 45px rgba(0,0,0,.28);transform:translateX(-50%)}
.aa-site-header .nav-has-submenu.is-open .about-submenu{display:grid}.aa-site-header .about-submenu a{padding:10px 11px;border-radius:8px;color:#dbe8f5;font-size:12px;font-weight:750;white-space:nowrap}.aa-site-header .about-submenu a:hover,.aa-site-header .about-submenu a.active{background:rgba(255,255,255,.1);color:#fff}
.aa-site-header .aa-nav-actions{display:flex;align-items:center;gap:9px;flex:0 0 auto}.aa-site-header .aa-login-button,.aa-site-header .aa-search-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:38px;margin:0;padding:0 13px;border:1px solid rgba(255,255,255,.34);border-radius:9px;background:rgba(2,21,39,.12);color:#fff;font:800 12px/1 var(--font-main,"Noto Sans",sans-serif);cursor:pointer;text-decoration:none}.aa-site-header .aa-login-button:hover,.aa-site-header .aa-search-button:hover{border-color:#ff741c;color:#fff;background:rgba(255,116,28,.16)}
.aa-site-header .aa-header-icon{border:0;background:transparent;color:#fff}.aa-site-header .aa-search-button{width:38px;padding:0}.aa-site-header .aa-search-button i{font-size:13px}.aa-site-header .aa-search-label{display:none}.aa-site-header .aa-language-switch{display:inline-flex;align-items:center;gap:6px;min-height:38px;color:#fff;font-size:12px;font-weight:850}.aa-site-header .aa-language-switch a{color:#fff;text-decoration:none}.aa-site-header .aa-language-switch i{font-size:12px}.aa-site-header .aa-mobile-menu-btn{display:none}
/* Every non-home page has an opaque, in-flow sticky header. It can never blend into the page hero. */
body:not(.aa-home-page) .aa-site-header{position:sticky;top:0;background:linear-gradient(100deg,#031a31 0%,#062b49 100%);box-shadow:0 7px 24px rgba(2,20,37,.18)}
body:not(.aa-home-page) .aa-site-header .aa-main-nav>a,body:not(.aa-home-page) .aa-site-header .aa-main-nav .nav-link{color:rgba(255,255,255,.9)}
body:not(.aa-home-page) .aa-site-header .aa-main-nav>a.active,body:not(.aa-home-page) .aa-site-header .aa-main-nav .nav-link.active{color:#fff}
/* Do not use an arbitrary article cover as a page banner; it often contains unrelated text. */
.articles-page .articles-hero{--articles-hero-image:radial-gradient(circle at 82% 28%,rgba(50,128,190,.25),transparent 22%),radial-gradient(circle at 72% 74%,rgba(11,61,104,.13),transparent 25%),linear-gradient(135deg,#edf5fb,#f8fbff)!important}
.articles-page .archive-article-card{min-height:174px!important}.articles-page .archive-article-cover{min-height:174px!important}.articles-page .archive-article-copy{padding:16px 19px!important}.articles-page .archive-read-more{margin-top:12px!important}
@media(max-width:1160px){.aa-site-header .aa-header-inner{width:calc(100% - 40px);gap:16px}.aa-site-header .aa-main-nav{gap:17px}.aa-site-header .aa-main-nav>a,.aa-site-header .aa-main-nav .nav-link{font-size:11px!important}.aa-site-header .aa-search-button{width:38px;padding:0}.aa-site-header .aa-login-button{padding-inline:11px}}
@media(max-width:991px){
  body:not(.aa-home-page) .aa-site-header{position:sticky!important}.aa-site-header .aa-header-inner{min-height:70px;width:calc(100% - 30px)}.aa-site-header .aa-brand .brand-logo{width:46px;height:46px}.aa-site-header .aa-brand .brand-text strong{font-size:12px}.aa-site-header .aa-brand .brand-text small{font-size:7px}
  body:not(.aa-home-page) .aa-site-header .aa-main-nav{position:fixed;top:70px;right:0;left:0;display:none;max-height:calc(100vh - 70px);padding:12px 15px 18px;overflow:auto;border-top:1px solid rgba(255,255,255,.1);background:#05223d;box-shadow:0 18px 34px rgba(0,0,0,.23)}
  body:not(.aa-home-page) .aa-site-header .aa-main-nav.open{display:grid;align-content:start;justify-content:stretch;gap:0}.aa-site-header .aa-main-nav>a,.aa-site-header .aa-main-nav .nav-link{justify-content:space-between;min-height:48px;padding:0 8px!important;font-size:13px!important}.aa-site-header .aa-main-nav>a:after,.aa-site-header .aa-main-nav .nav-link:after{display:none}.aa-site-header .nav-has-submenu{display:block}.aa-site-header .about-submenu{position:static;min-width:0;margin:0 8px 8px;transform:none;background:rgba(255,255,255,.06);box-shadow:none}.aa-site-header .aa-mobile-menu-btn{display:grid;place-items:center;width:38px;height:38px;padding:0;border:1px solid rgba(255,255,255,.28);border-radius:9px;background:transparent;color:#fff;font-size:17px}.aa-site-header .aa-nav-actions{gap:7px}.aa-site-header .aa-login-button{display:none}.aa-site-header .aa-language-switch{display:none}
}
@media(max-width:520px){.aa-site-header .aa-brand .brand-text{display:none}.aa-site-header .aa-header-inner{width:calc(100% - 24px)}.aa-site-header .aa-search-button{width:36px;min-height:36px}.aa-site-header .aa-mobile-menu-btn{width:36px;height:36px}}
</style>
<header class="site-header aa-site-header" id="siteHeader" data-block-uuid="{{ $block->block_uuid }}">
    <div class="container aa-header-inner">
        @if($enabled('show_brand'))
            <a href="{{ $legacyUrl('/') }}" class="brand aa-brand">
                <span class="brand-logo {{ $headerLogo !== '' ? 'has-image' : '' }}">
                    @if($headerLogo !== '')
                        <img src="{{ $publicFile($headerLogo) }}" alt="{{ $siteName }}">
                    @else
                        <i class="fa-solid fa-shield-heart"></i>
                    @endif
                </span>
                <span class="brand-text">
                    <strong data-entity="setting" data-entity-id="site_name" data-entity-field="setting_value">{{ $siteName }}</strong>
                    <small data-entity="setting" data-entity-id="site_slogan" data-entity-field="setting_value">{{ $siteSlogan }}</small>
                </span>
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
                        <div class="nav-item nav-has-submenu {{ $isActive ? 'active' : '' }}" data-about-menu>
                            <button type="button" class="nav-link {{ $isActive ? 'active' : '' }}" data-about-submenu-toggle aria-expanded="false">
                                <span data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <div class="about-submenu" data-about-submenu>
                                @foreach($menu->children as $child)
                                    <a href="{{ $legacyUrl((string) $child->url) }}" target="{{ $child->target ?: '_self' }}" data-entity="menu" data-entity-id="{{ $child->id }}" data-entity-field="title">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        </div>
                    @elseif($isAbout)
                        <div class="nav-item nav-has-submenu {{ $isActive ? 'active' : '' }}" data-about-menu>
                            <button type="button" class="nav-link {{ $isActive ? 'active' : '' }}" data-about-submenu-toggle aria-expanded="false">
                                <span data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <div class="about-submenu" data-about-submenu>
                                @foreach($aboutSubsections as $section)
                                    <a class="{{ $isActive && $activeAboutSection === $section['key'] ? 'active' : '' }}" href="{{ $legacyUrl('/about?section='.$section['key']) }}">{{ $section['title'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a class="{{ $isActive ? 'active' : '' }}" href="{{ $legacyUrl((string) $menu->url) }}" target="{{ $menu->target ?: '_self' }}" data-entity="menu" data-entity-id="{{ $menu->id }}" data-entity-field="title">{{ $menu->title }}</a>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="nav-actions aa-nav-actions" id="navActions">
            @if($enabled('show_search'))
                <button class="aa-header-icon aa-search-button search-btn" type="button" aria-label="{{ $ui['search'] }}" data-site-search-open>
                    <i class="fa-solid fa-magnifying-glass"></i><span class="aa-search-label">{{ $ui['search'] }}</span>
                </button>
            @endif
            @if($enabled('show_auth'))
                @if($currentUser)
                    <a href="{{ route('profile', ['lang' => $lang]) }}" class="aa-user-link">{{ $currentUser->full_name }}</a>
                    <a href="{{ route('site.logout') }}" class="aa-login-button">{{ $siteSettings['auth_logout'] ?? 'Çıxış' }}</a>
                @else
                    <button type="button" class="aa-login-button" data-auth-open="login">{{ $siteSettings['btn_login'] ?? 'Daxil ol' }}</button>
                @endif
            @endif
            @if($enabled('show_languages'))
                <div class="aa-language-switch">
                    <i class="fa-solid fa-globe"></i>
                    @foreach($languages->filter(fn($language) => $language->code !== $lang)->take(1) as $language)
                        <a href="{{ app(\App\Services\Site\SeoService::class)->alternate(request(), $language->code) }}">{{ strtoupper($language->code) }}</a>
                    @endforeach
                </div>
            @endif
            @if($enabled('show_navigation'))
                <button class="mobile-menu-btn aa-mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="Menu" aria-controls="mainNav" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
            @endif
        </div>
    </div>
</header>
