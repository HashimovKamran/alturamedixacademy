@php
    $siteName = $settings['site_name'] ?? 'ALTURAMEDIX ACADEMY';
    $siteSlogan = $settings['site_slogan'] ?? 'Academy of Emergency & Critical Care';
    $logoImage = trim((string)($settings['header_logo_image'] ?? '')) ?: trim((string)($settings['logo_image'] ?? ''));
    $publicFile = fn (?string $path) => $path ? asset(ltrim($path, '/')) : '';
    $legacyUrl = fn (string $path) => \App\Support\CleanUrl::to($path, $lang);
    $ui = \App\Support\PublicUiText::all($lang);
    $aboutSubsections = match ($lang) {
        'en' => [
            ['key' => 'who', 'title' => 'Who we are'],
            ['key' => 'mission', 'title' => 'Our mission'],
            ['key' => 'board', 'title' => 'Management board'],
        ],
        'ru' => [
            ['key' => 'who', 'title' => 'Кто мы'],
            ['key' => 'mission', 'title' => 'Наша миссия'],
            ['key' => 'board', 'title' => 'Руководство'],
        ],
        'tr' => [
            ['key' => 'who', 'title' => 'Biz kimiz'],
            ['key' => 'mission', 'title' => 'Misyonumuz'],
            ['key' => 'board', 'title' => 'Yönetim ekibi'],
        ],
        default => [
            ['key' => 'who', 'title' => 'Biz kimik'],
            ['key' => 'mission', 'title' => 'Missiyamız'],
            ['key' => 'board', 'title' => 'İdarə heyəti'],
        ],
    };
    $activeAboutSection = in_array((string) request()->query('section'), ['who', 'mission', 'board'], true)
        ? (string) request()->query('section')
        : 'who';
    $isHomepage = ($activePage ?? '') === 'index';
@endphp
<!doctype html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $siteName)</title>
    <meta name="description" content="@yield('meta_description', $settings['site_description'] ?? '')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <link rel="canonical" href="{{ app(\App\Services\Site\SeoService::class)->canonical(request(), $lang) }}">
    @foreach($languages as $seoLanguage)
        <link rel="alternate" hreflang="{{ $seoLanguage->code }}" href="{{ app(\App\Services\Site\SeoService::class)->alternate(request(), $seoLanguage->code) }}">
    @endforeach
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', $siteName)">
    <meta property="og:description" content="@yield('meta_description', $settings['site_description'] ?? '')">
    <meta property="og:url" content="{{ app(\App\Services\Site\SeoService::class)->canonical(request(), $lang) }}">
    @hasSection('meta_image')<meta property="og:image" content="@yield('meta_image')">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Noto+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page-builder.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laravel-fixes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/alturamedix-home.css') }}">
    @stack('styles')
</head>
<body class="{{ $isHomepage ? 'aa-home-page' : '' }}">
@include('public.partials.composition', ['pageBuilderDocument' => $headerBuilderDocument ?? null, 'pageBuilderBlocks' => $headerBuilderBlocks ?? collect(), 'templatePart' => true])

@yield('content')

@include('public.partials.composition', ['pageBuilderDocument' => $footerBuilderDocument ?? null, 'pageBuilderBlocks' => $footerBuilderBlocks ?? collect(), 'templatePart' => true])

@php
    $authModal = session('auth_flash_modal', old('auth_modal', ''));
    $authError = session('auth_flash_error') ?: ($errors->any() ? $errors->first() : '');
@endphp
<div class="auth-backdrop {{ $authModal ? 'is-open' : '' }}" id="authBackdrop" data-open-modal="{{ $authModal }}">
    <div class="auth-modal" role="dialog" aria-modal="true">
        <div class="auth-tabs">
            <button type="button" class="auth-tab {{ $authModal !== 'register' ? 'active' : '' }}" data-auth-tab="login">{{ $settings['auth_login_button'] ?? 'Daxil ol' }}</button>
            <button type="button" class="auth-tab {{ $authModal === 'register' ? 'active' : '' }}" data-auth-tab="register">{{ $settings['auth_register_button'] ?? $ui['register_now'] }}</button>
        </div>
        @if($authError)<div class="auth-alert">{{ $authError }}</div>@endif
        <form method="post" action="{{ route('site.login') }}" class="auth-form {{ $authModal !== 'register' ? 'active' : '' }}" data-auth-form="login">
            @csrf
            <h3>{{ $settings['auth_login_title'] ?? 'Daxil ol' }}</h3>
            <p>{{ $settings['auth_login_subtitle'] ?? '' }}</p>
            <label>{{ $settings['auth_email'] ?? 'Email hesabı' }}</label>
            <input type="email" name="email" required>
            <label>{{ $settings['auth_password'] ?? 'Şifrə' }}</label>
            <input type="password" name="password" required>
            <button type="submit" class="auth-submit">{{ $settings['auth_login_button'] ?? 'Daxil ol' }}</button>
            <a class="auth-google-link" href="{{ route('site.google.login', ['lang' => $lang]) }}"><i class="fa-brands fa-google"></i> {{ $ui['google_login'] }}</a>
        </form>
        <form method="post" action="{{ route('site.register') }}" class="auth-form {{ $authModal === 'register' ? 'active' : '' }}" data-auth-form="register">
            @csrf
            <input type="hidden" name="auth_modal" value="register">
            <h3>{{ $settings['auth_register_title'] ?? $ui['register_now'] }}</h3>
            <p>{{ $settings['auth_register_subtitle'] ?? '' }}</p>
            <label>{{ $ui['full_name'] }}</label><input type="text" name="full_name" required>
            <label>{{ $ui['phone'] }}</label><input type="text" name="phone">
            <label>{{ $settings['auth_email'] ?? 'Email hesabı' }}</label><input type="email" name="email" required>
            <div class="auth-two">
                <div><label>{{ $settings['auth_password'] ?? 'Şifrə' }}</label><input type="password" name="password" required></div>
                <div><label>{{ $ui['password_confirmation'] }}</label><input type="password" name="password_confirmation" required></div>
            </div>
            <button type="submit" class="auth-submit">{{ $settings['auth_register_button'] ?? $ui['register_now'] }}</button>
            <a class="auth-google-link" href="{{ route('site.google.login', ['lang' => $lang]) }}"><i class="fa-brands fa-google"></i> {{ $ui['google_register'] }}</a>
        </form>
    </div>
</div>

<div class="site-search-backdrop" id="siteSearch" aria-hidden="true">
    <div class="site-search-modal" role="dialog" aria-modal="true">
        <div class="site-search-head">
            <input type="search" id="siteSearchInput" placeholder="{{ $ui['search'] }}">
            <button class="site-search-close" type="button" data-site-search-close><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="site-search-results" id="siteSearchResults">
            <div class="site-search-empty">{{ $ui['search_min_chars'] }}</div>
        </div>
    </div>
</div>

<script src="{{ asset('js/main.js') }}"></script>
<script>
(function(){
    const backdrop = document.getElementById('authBackdrop');
    if (!backdrop) return;
    const switchTab = tab => {
        document.querySelectorAll('[data-auth-tab]').forEach(el => el.classList.toggle('active', el.dataset.authTab === tab));
        document.querySelectorAll('[data-auth-form]').forEach(el => el.classList.toggle('active', el.dataset.authForm === tab));
    };
    const open = tab => { backdrop.classList.add('is-open'); document.body.classList.add('auth-locked'); switchTab(tab || 'login'); };
    const close = () => { backdrop.classList.remove('is-open'); document.body.classList.remove('auth-locked'); };
    document.querySelectorAll('[data-auth-open]').forEach(el => el.addEventListener('click', () => open(el.dataset.authOpen)));
    backdrop.addEventListener('click', event => { if (event.target === backdrop) close(); });
    document.querySelectorAll('[data-auth-tab]').forEach(el => el.addEventListener('click', () => switchTab(el.dataset.authTab)));
    if (backdrop.classList.contains('is-open')) document.body.classList.add('auth-locked');
    window.AAAuthModalOpen = function(tab){ open(tab); return false; };
})();
</script>
<script>
(function(){
    const backdrop = document.getElementById('siteSearch');
    const input = document.getElementById('siteSearchInput');
    const results = document.getElementById('siteSearchResults');
    if (!backdrop || !input || !results) return;
    let timer = null;
    const lang = @json($lang);
    const searchUrl = @json(route('search.api'));
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&gt;','>':'&quot;','"':'&quot;',"'":'&#039;'}[char]));
    const open = () => { backdrop.classList.add('is-open'); backdrop.setAttribute('aria-hidden', 'false'); document.body.classList.add('auth-locked'); setTimeout(() => input.focus(), 40); };
    const close = () => { backdrop.classList.remove('is-open'); backdrop.setAttribute('aria-hidden', 'true'); document.body.classList.remove('auth-locked'); };
    const empty = (text) => { results.innerHTML = '<div class="site-search-empty">' + escapeHtml(text) + '</div>'; };
    const render = (items) => {
        if (!items.length) { empty(@json($ui['search_no_results'])); return; }
        results.innerHTML = items.map(item => `<a class="site-search-result" href="${escapeHtml(item.url)}"><i class="${escapeHtml(item.icon)}"></i><span><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.description || '')}</span></span><small>${escapeHtml(item.type)}</small></a>`).join('');
    };
    const run = async () => {
        const q = input.value.trim();
        if (q.length < 2) { empty(@json($ui['search_min_chars'])); return; }
        empty(@json($ui['searching']));
        const response = await fetch(searchUrl + '?q=' + encodeURIComponent(q) + '&lang=' + encodeURIComponent(lang), {headers: {'Accept': 'application/json'}});
        const data = await response.json();
        render(data.results || []);
    };
    const debounce = () => { clearTimeout(timer); timer = setTimeout(run, 220); };
    document.querySelectorAll('[data-site-search-open]').forEach(button => button.addEventListener('click', open));
    document.querySelectorAll('[data-site-search-close]').forEach(button => button.addEventListener('click', close));
    backdrop.addEventListener('click', event => { if (event.target === backdrop) close(); });
    input.addEventListener('input', debounce);
    window.addEventListener('keydown', event => { if (event.key === 'Escape' && backdrop.classList.contains('is-open')) close(); });
})();
</script>
@stack('scripts')
</body>
</html>