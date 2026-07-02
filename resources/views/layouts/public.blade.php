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
    @stack('styles')
</head>
<body class="{{ $isHomepage ? 'aa-home-page' : '' }}">
@include('public.partials.composition', ['pageBuilderDocument' => $headerBuilderDocument ?? null, 'pageBuilderBlocks' => $headerBuilderBlocks ?? collect(), 'templatePart' => true])

<div class="site-search-inline" id="siteSearch" aria-hidden="true" hidden>
    <div class="site-search-panel" role="dialog" aria-modal="true" aria-label="{{ $ui['search'] }}">
        <div class="site-search-head">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="siteSearchInput" placeholder="{{ $ui['search'] }}" minlength="3" autocomplete="off">
            <span class="site-search-esc">ESC</span>
            <button class="site-search-close" type="button" data-site-search-close aria-label="{{ $ui['search'] }}"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="site-search-grid">
            <aside class="site-search-sidebar" aria-label="Axtarış filtrləri">
                <section class="site-search-side-section">
                    <div class="site-search-side-head">
                        <h3>Son axtarışlar</h3>
                        <button type="button" data-search-clear>Təmizlə</button>
                    </div>
                    <button type="button" class="site-search-history" data-search-suggest="acls provider course"><i class="fa-regular fa-clock"></i><span>acls provider course</span><i class="fa-solid fa-xmark"></i></button>
                    <button type="button" class="site-search-history" data-search-suggest="phtls provider"><i class="fa-regular fa-clock"></i><span>phtls provider</span><i class="fa-solid fa-xmark"></i></button>
                    <button type="button" class="site-search-history" data-search-suggest="advanced airway management"><i class="fa-regular fa-clock"></i><span>advanced airway management</span><i class="fa-solid fa-xmark"></i></button>
                    <button type="button" class="site-search-history" data-search-suggest="sepsis biomarkerlər"><i class="fa-regular fa-clock"></i><span>sepsis biomarkerlər</span><i class="fa-solid fa-xmark"></i></button>
                    <button type="button" class="site-search-history" data-search-suggest="reanimasiya prinsipləri"><i class="fa-regular fa-clock"></i><span>reanimasiya prinsipləri</span><i class="fa-solid fa-xmark"></i></button>
                </section>
                <section class="site-search-side-section">
                    <h3>Populyar mövzular</h3>
                    <div class="site-search-tags">
                        <button type="button" data-search-suggest="ACLS">ACLS</button>
                        <button type="button" data-search-suggest="PHTLS">PHTLS</button>
                        <button type="button" data-search-suggest="Reanimasiya">Reanimasiya</button>
                        <button type="button" data-search-suggest="Sepsis">Sepsis</button>
                        <button type="button" data-search-suggest="Ultrasəs">Ultrasəs</button>
                        <button type="button" data-search-suggest="Toksikologiya">Toksikologiya</button>
                        <button type="button" data-search-suggest="Pediatriya">Pediatriya</button>
                        <button type="button" data-search-suggest="Kritik baxım">Kritik baxım</button>
                    </div>
                </section>
                <section class="site-search-side-section">
                    <h3>Kateqoriyalar</h3>
                    <div class="site-search-categories">
                        <a href="{{ $legacyUrl('/articles') }}"><i class="fa-regular fa-newspaper"></i><span>Akademik yazılar</span><small>1,248</small></a>
                        <a href="{{ $legacyUrl('/trainings') }}"><i class="fa-solid fa-graduation-cap"></i><span>Təlimlər</span><small>86</small></a>
                        <a href="{{ $legacyUrl('/certificates') }}"><i class="fa-regular fa-id-card"></i><span>Sertifikatlar</span><small>312</small></a>
                        <a href="{{ $legacyUrl('/gallery') }}"><i class="fa-regular fa-circle-play"></i><span>Video materiallar</span><small>64</small></a>
                    </div>
                </section>
                <a class="site-search-help" href="{{ $legacyUrl('/contact') }}">
                    <i class="fa-regular fa-circle-question"></i>
                    <span><strong>Axtardığınızı tapa bilmirsiniz?</strong><small>Bizimlə əlaqə saxlayın</small></span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </aside>
            <section class="site-search-main">
                <div class="site-search-results-head">
                    <h3>Nəticələr <span id="siteSearchCount"></span></h3>
                    <a href="{{ $legacyUrl('/articles') }}">Hamısına bax <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="site-search-results" id="siteSearchResults">
                    <div class="site-search-empty">{{ $ui['search_min_chars'] }}</div>
                </div>
            </section>
        </div>
        <div class="site-search-foot">
            <span><i class="fa-solid fa-magnifying-glass"></i> Daha dəqiq nəticələr üçün filtrlərdən istifadə edin</span>
            <button type="button">Filtrləri aç <i class="fa-solid fa-sliders"></i></button>
        </div>
    </div>
</div>

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
    const searchPanel = document.getElementById('siteSearch');
    const input = document.getElementById('siteSearchInput');
    const results = document.getElementById('siteSearchResults');
    const count = document.getElementById('siteSearchCount');
    if (!searchPanel || !input || !results) return;
    let timer = null;
    const minChars = 3;
    const lang = @json($lang);
    const searchUrl = @json(route('search.api'));
    const minMessage = @json($ui['search_min_chars']);
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const empty = (text) => {
        if (count) count.textContent = '';
        results.innerHTML = '<div class="site-search-empty">' + escapeHtml(text) + '</div>';
    };
    const syncButtons = (expanded) => {
        document.querySelectorAll('[data-site-search-open]').forEach(button => button.setAttribute('aria-expanded', expanded ? 'true' : 'false'));
    };
    const open = () => {
        searchPanel.hidden = false;
        searchPanel.setAttribute('aria-hidden', 'false');
        document.body.classList.add('site-search-open');
        syncButtons(true);
        setTimeout(() => {
            input.focus();
        }, 40);
    };
    const close = () => {
        searchPanel.hidden = true;
        searchPanel.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('site-search-open');
        syncButtons(false);
        input.value = '';
        empty(minMessage);
    };
    const groupLabel = (item) => {
        const icon = String(item.icon || '');
        if (icon.includes('graduation-cap')) return 'Təlimlər';
        if (icon.includes('newspaper')) return 'Akademik yazılar';
        if (icon.includes('id-card')) return 'Sertifikatlar';
        if (icon.includes('layer-group')) return 'Kateqoriyalar';
        if (icon.includes('file-lines')) return 'Səhifələr';
        return String(item.type || 'Nəticələr');
    };
    const metaFor = (item) => {
        const label = groupLabel(item);
        if (label === 'Təlimlər') return '<i class="fa-regular fa-calendar"></i> Təlim <i class="fa-solid fa-location-dot"></i> Bakı, Azərbaycan';
        if (label === 'Akademik yazılar') return '<i class="fa-regular fa-calendar"></i> Akademik yazı <i class="fa-solid fa-tag"></i> Məqalə';
        if (label === 'Sertifikatlar') return '<i class="fa-regular fa-id-card"></i> Sertifikat <i class="fa-solid fa-globe"></i> Beynəlxalq';
        return '<i class="fa-regular fa-file-lines"></i> ' + escapeHtml(label);
    };
    const resultCard = (item) => `
        <a class="site-search-result" href="${escapeHtml(item.url)}">
            <span class="site-search-result-media"><i class="${escapeHtml(item.icon || 'fa-solid fa-magnifying-glass')}"></i></span>
            <span class="site-search-result-copy">
                <strong>${escapeHtml(item.title)}</strong>
                <span>${escapeHtml(item.description || '')}</span>
                <small>${metaFor(item)}</small>
            </span>
            <span class="site-search-result-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>`;
    const render = (items, total) => {
        if (!items.length) { empty(@json($ui['search_no_results'])); return; }
        if (count) count.textContent = '(' + Number(total || items.length).toLocaleString('az-AZ') + ')';
        const grouped = items.reduce((carry, item) => {
            const label = groupLabel(item);
            if (!carry[label]) carry[label] = [];
            carry[label].push(item);
            return carry;
        }, {});
        results.innerHTML = Object.entries(grouped).slice(0, 5).map(([label, group]) => `
            <section class="site-search-group">
                <div class="site-search-group-head"><h4>${escapeHtml(label)} (${group.length})</h4></div>
                ${group.slice(0, 3).map(resultCard).join('')}
                <a class="site-search-group-all" href="${escapeHtml(group[0].url)}">Hamısına bax <i class="fa-solid fa-arrow-right"></i></a>
            </section>
        `).join('');
    };
    const run = async () => {
        const q = input.value.trim();
        if (q.length < minChars) { empty(minMessage); return; }
        empty(@json($ui['searching']));
        try {
            const response = await fetch(searchUrl + '?q=' + encodeURIComponent(q) + '&lang=' + encodeURIComponent(lang), {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            render(data.results || [], data.count);
        } catch (error) {
            empty(@json($ui['search_no_results']));
        }
    };
    const debounce = () => {
        clearTimeout(timer);
        if (input.value.trim().length < minChars) { empty(minMessage); return; }
        timer = setTimeout(run, 220);
    };
    document.querySelectorAll('[data-site-search-open]').forEach(button => {
        button.setAttribute('aria-controls', 'siteSearch');
        button.setAttribute('aria-expanded', 'false');
        button.addEventListener('click', open);
    });
    document.querySelectorAll('[data-site-search-close]').forEach(button => button.addEventListener('click', close));
    document.querySelectorAll('[data-search-suggest]').forEach(button => button.addEventListener('click', () => {
        input.value = button.dataset.searchSuggest || '';
        input.focus();
        run();
    }));
    document.querySelectorAll('[data-search-clear]').forEach(button => button.addEventListener('click', () => {
        input.value = '';
        input.focus();
        empty(minMessage);
    }));
    searchPanel.addEventListener('click', event => { if (event.target === searchPanel) close(); });
    input.addEventListener('input', debounce);
    window.addEventListener('keydown', event => { if (event.key === 'Escape' && !searchPanel.hidden) close(); });
})();
</script>
@stack('scripts')
</body>
</html>
