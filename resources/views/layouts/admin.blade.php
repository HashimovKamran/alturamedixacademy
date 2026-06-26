<!doctype html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin panel')</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root{
            --admin-bg:#e7f5f7;
            --admin-shell:#deeeeb;
            --admin-sidebar:#deeeeb;
            --admin-card:#fff;
            --admin-text:#151719;
            --admin-muted:#63747a;
            --admin-soft:#eef8f6;
            --admin-line:#d8e9e7;
            --admin-line-2:#e8f1ef;
            --admin-accent:#ffd166;
            --admin-accent-soft:#fff3cc;
            --admin-blue:#78a7e8;
            --admin-blue-soft:#eaf2ff;
            --admin-green:#a8e36e;
            --admin-green-soft:#effbde;
            --admin-mint:#75d6c8;
            --admin-mint-soft:#e7f8f5;
            --admin-danger:#c94c5a;
            --admin-success:#2f9b6d;
            --admin-shadow:0 26px 80px rgba(53,117,126,.13);
        }
        *{box-sizing:border-box}
        body{margin:0;background:radial-gradient(circle at 9% 6%,rgba(184,229,231,.75),transparent 31%),radial-gradient(circle at 92% 2%,rgba(226,233,255,.82),transparent 29%),linear-gradient(135deg,#edf9f8 0%,var(--admin-bg) 48%,#e8f1fb 100%);color:var(--admin-text);font-family:"Noto Sans","Segoe UI",Arial,sans-serif;font-weight:650}
        a{text-decoration:none;color:inherit}
        .admin-layout{width:100%;min-height:100vh;margin:0;display:grid;grid-template-columns:260px minmax(0,1fr);align-items:stretch;background:var(--admin-shell);border:0;border-radius:0;box-shadow:none;overflow:hidden;transition:grid-template-columns .2s ease}
        .admin-sidebar{background:var(--admin-sidebar);color:var(--admin-text);padding:8px 12px 18px;position:relative;min-height:100vh;height:auto;overflow:visible;border-right:0}
        .admin-sidebar::-webkit-scrollbar{width:7px}
        .admin-sidebar::-webkit-scrollbar-thumb{background:#c5dbd7;border-radius:999px}
        .brand{min-height:76px;display:flex;align-items:center;gap:10px;padding:0 20px;border-bottom:1px solid var(--admin-line-2);margin:0 -12px 14px}
        .brand-icon{width:34px;height:34px;border-radius:10px;background:#111;color:var(--admin-accent);display:grid;place-items:center;font-size:18px;box-shadow:inset 0 0 0 1px rgba(255,255,255,.14)}
        .brand strong{display:block;color:#111;font-size:14px;font-weight:900;letter-spacing:-.01em}
        .brand small{display:block;color:var(--admin-muted);font-size:11px;margin-top:2px;font-weight:750}
        .sidebar-toggle{position:absolute;top:23px;right:-14px;z-index:30;width:30px;height:30px;border:1px solid var(--admin-line);border-radius:10px;background:#fff;color:#111;display:grid;place-items:center;cursor:pointer;box-shadow:0 10px 24px rgba(61,125,131,.12);font-size:17px;line-height:1;transition:transform .18s ease,background-color .18s ease}
        .sidebar-toggle:hover{background:#fffaf0;transform:translateY(-1px)}
        .admin-nav{display:flex;flex-direction:column;min-height:auto}
        .admin-nav a{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;color:#2d3236;margin-bottom:3px;font-size:13px;font-weight:780;line-height:1.25;position:relative;transition:background-color .16s ease,color .16s ease}
        .admin-nav a i{width:28px;height:28px;border-radius:9px;display:grid;place-items:center;color:#718084;font-size:17px;background:transparent;line-height:1}
        .admin-nav a:hover{background:#eff9f7;color:#111}
        .admin-nav a.active{background:#fff;color:#111;box-shadow:0 1px 0 rgba(255,255,255,.75),0 10px 24px rgba(61,125,131,.09)}
        .admin-nav a.active::before{display:none}
        .admin-nav a.active i{color:#111;background:rgba(253, 211, 144)}
        .nav-title{margin:16px 10px 7px;color:#7c9297;font-size:11px;font-weight:850;letter-spacing:0}
        .admin-layout.sidebar-collapsed{grid-template-columns:76px minmax(0,1fr)}
        .admin-layout.sidebar-collapsed .admin-sidebar{padding-left:8px;padding-right:8px}
        .admin-layout.sidebar-collapsed .brand{justify-content:center;padding:0 8px;margin:0 -8px 14px}
        .admin-layout.sidebar-collapsed .brand>div:not(.brand-icon){display:none}
        .admin-layout.sidebar-collapsed .admin-nav a{justify-content:center;gap:0;padding:9px 8px;font-size:0}
        .admin-layout.sidebar-collapsed .admin-nav a i{font-size:18px}
        .admin-layout.sidebar-collapsed .nav-title{height:1px;margin:14px 12px 8px;background:rgba(99,116,122,.18);font-size:0;overflow:hidden}
        .admin-layout.sidebar-collapsed .sidebar-toggle i{transform:rotate(180deg)}
        .admin-main{min-width:0;align-self:stretch;margin:8px 8px 8px 0;background:rgba(248,251,250,.92);border:1px solid rgba(208,229,226,.95);border-radius:20px;box-shadow:0 18px 48px rgba(61,125,131,.075);overflow:hidden}
        .admin-top{min-height:76px;background:rgba(248,251,250,.92);border-bottom:1px solid var(--admin-line-2);display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 26px;position:sticky;top:0;z-index:20;backdrop-filter:blur(14px)}
        .top-title{min-width:0}
        .breadcrumbs{display:flex;align-items:center;gap:8px;color:var(--admin-muted);font-size:12px;font-weight:800;margin-bottom:5px}
        .breadcrumbs i{font-size:13px;color:#99adb2}
        .admin-top h1{margin:0;font-size:22px;line-height:1.2;font-weight:900;letter-spacing:-.025em;color:#111}
        .top-actions{display:flex;align-items:center;gap:10px;flex:0 0 auto}
        .admin-language{height:44px;display:flex;align-items:center;gap:5px;background:#fff;border:1px solid var(--admin-line);border-radius:14px;padding:5px;box-shadow:0 10px 24px rgba(61,125,131,.055)}
        .admin-language i{font-size:17px;color:#6f7d82}
        .admin-language form{margin:0}
        .admin-language button{height:32px;min-width:38px;border:0;border-radius:10px;background:transparent;color:#63747a;font:inherit;font-size:12px;font-weight:900;cursor:pointer;padding:0 10px;transition:background-color .16s ease,color .16s ease,box-shadow .16s ease}
        .admin-language button:hover{background:#f3faf8;color:#111}
        .admin-language button.active{background:rgba(255,209,102,.58);color:#111;box-shadow:0 8px 16px rgba(255,209,102,.18)}
        .admin-profile{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--admin-line);border-radius:14px;padding:7px 10px;box-shadow:0 10px 24px rgba(61,125,131,.07)}
        .admin-avatar{width:30px;height:30px;border-radius:50%;display:grid;place-items:center;background:#111;color:#fff;font-size:12px;font-weight:900}
        .admin-name{display:block;font-size:13px;color:#111;font-weight:900;white-space:nowrap}
        .admin-role{display:block;font-size:11px;color:var(--admin-muted);font-weight:750;margin-top:1px}
        .admin-content{min-height:calc(100vh - 76px);padding:24px 26px 30px;background:rgba(248,251,250,.92)}
        .card{background:var(--admin-card);border:1px solid var(--admin-line-2);border-radius:16px;padding:20px;box-shadow:0 14px 38px rgba(61,125,131,.055);margin-bottom:18px}
        .card h2{margin:0 0 16px;font-size:18px;font-weight:900;letter-spacing:-.02em;color:#111}
        .grid{display:grid;gap:16px}
        .grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
        .grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}
        .grid-4{grid-template-columns:repeat(4,minmax(0,1fr))}
        .grid-4>.card{padding:16px}
        .grid-4>.card h2{margin-bottom:6px;font-size:27px;line-height:1;font-weight:900;letter-spacing:-.025em}
        label{display:block;margin:0 0 6px;font-size:12px;font-weight:900;color:#1f2327}
        input,textarea,select{width:100%;border:1px solid var(--admin-line);border-radius:11px;padding:10px 12px;font-family:inherit;font-size:13px;font-weight:700;outline:0;background:#fff;color:#111;transition:border-color .16s ease,box-shadow .16s ease,background-color .16s ease}
        textarea{min-height:116px;resize:vertical}
        input:focus,textarea:focus,select:focus{border-color:#9fd7d0;box-shadow:0 0 0 4px rgba(117,214,200,.16)}
        input[type="checkbox"]{width:auto;accent-color:var(--admin-accent)}
        table{width:100%;border-collapse:separate;border-spacing:0}
        th,td{padding:12px;border-bottom:1px solid var(--admin-line-2);text-align:left;vertical-align:middle;font-size:13px}
        th{font-size:11px;color:#6e858a;font-weight:900;background:#f3faf8;text-transform:none}
        tr:last-child td{border-bottom:0}
        tbody tr:hover td{background:#f8fdfb}
        .btn{border:1px solid transparent;border-radius:11px;padding:9px 13px;font-weight:900;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;font-family:inherit;font-size:13px;text-decoration:none;background:#111;color:#fff;line-height:1.2;transition:transform .16s ease,box-shadow .16s ease,background-color .16s ease,border-color .16s ease}
        .btn i{font-size:16px;line-height:1;color:currentColor}
        .btn:hover{transform:translateY(-1px)}
        .btn-primary{background:var(--admin-accent);color:#111;box-shadow:0 10px 22px rgba(255,209,102,.28)}
        .btn-dark{background:#111;color:#fff}
        .btn-light{background:#fff;color:#111;border-color:var(--admin-line);box-shadow:0 6px 18px rgba(25,26,27,.035)}
        .btn-danger{background:var(--admin-danger);color:#fff}
        .btn-green{background:var(--admin-success);color:#fff}
        .action-list{display:flex;align-items:center;gap:7px;flex-wrap:wrap}
        .action-icon.btn{width:36px;height:36px;padding:0;border-radius:11px}
        .action-icon.btn i{font-size:18px}
        .action-icon.btn-light{color:#111}
        .action-icon.btn-green{background:#eaf9ef;color:#1f8f4d;border-color:#c7efd3}
        .action-icon.btn-danger{background:#fff1f2;color:#c94c5a;border-color:#ffd0d5}
        .badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 8px;font-size:11px;font-weight:850;border:1px solid transparent;line-height:1.2}
        .badge-green{background:var(--admin-green-soft);color:#2f7d35;border-color:#cdeeb0}
        .badge-gray{background:#f1f7f8;color:#597077;border-color:#dbe9e8}
        .badge-orange{background:var(--admin-accent-soft);color:#8a5c00;border-color:#ffe39b}
        .alert,.alert-ok,.alert-error{border-radius:13px;padding:12px 14px;margin-bottom:16px;font-weight:850;font-size:13px}
        .alert-ok{background:var(--admin-green-soft);border:1px solid #cdeeb0;color:#2f7d35}
        .alert-error{background:#fff1f1;border:1px solid #fecaca;color:#b42318}
        .preview-img{width:82px;height:56px;object-fit:cover;border-radius:10px;border:1px solid var(--admin-line);background:var(--admin-soft)}
        .muted{color:var(--admin-muted);font-size:13px;font-weight:750}
        .help{margin-top:7px;color:var(--admin-muted);font-size:12px;font-weight:700;line-height:1.5}
        .pagination{display:flex;gap:6px;flex-wrap:wrap}
        @media(max-width:1100px){
            body{background:var(--admin-shell)}
            .admin-layout{width:100%;min-height:100vh;margin:0;border:0;border-radius:0;grid-template-columns:1fr}
            .admin-sidebar{position:relative;top:auto;height:auto;border-right:0;border-bottom:1px solid var(--admin-line)}
            .admin-main{margin:0;border:0;border-radius:0;box-shadow:none}
            .grid-2,.grid-3,.grid-4{grid-template-columns:1fr}
            .admin-top{position:relative;flex-wrap:wrap}
            .top-actions{width:100%;justify-content:space-between}
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $admin = session('admin_user_id') ? \App\Models\AdminUser::query()->find(session('admin_user_id')) : null;
    $adminInitial = $admin ? mb_substr(trim($admin->full_name ?: 'A'), 0, 1, 'UTF-8') : 'A';
    $adminLanguages = \App\Support\Admin\AdminLanguage::activeLanguages();
    $adminSelectedLanguage = \App\Support\Admin\AdminLanguage::selected(request());
    $moduleIcons = [
        'menus' => 'ti ti-menu-2',
        'pages' => 'ti ti-file-text',
        'sliders' => 'ti ti-photo',
        'stats' => 'ti ti-chart-bar',
        'categories' => 'ti ti-category',
        'articles' => 'ti ti-news',
        'trainings' => 'ti ti-school',
        'features' => 'ti ti-stethoscope',
        'blocks' => 'ti ti-box',
        'partners' => 'ti ti-users-group',
        'ads' => 'ti ti-ad-2',
        'gallery' => 'ti ti-photo',
        'certificates_manage' => 'ti ti-award',
    ];
    $moduleNavLabels = [
        'articles' => 'Akademik yazılar',
    ];
@endphp
<div class="admin-layout" data-admin-layout>
    <aside class="admin-sidebar">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Sidebar menyusunu bağla" aria-expanded="true">
            <i class="ti ti-chevrons-left"></i>
        </button>
        <div class="brand">
            <div class="brand-icon"><i class="ti ti-shield-heart"></i></div>
            <div>
                <strong>Alturamedix</strong>
                <small>İdarə paneli</small>
            </div>
        </div>

        <nav class="admin-nav">
            <a class="{{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.view') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="ti ti-chart-line"></i> İdarə paneli</a>

            <div class="nav-title">Sayt idarəsi</div>
            <a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}"><i class="ti ti-settings"></i> Sayt ayarları</a>
            @foreach(['menus', 'sliders'] as $moduleKey)
                @php($module = \App\Support\Admin\ContentModuleRegistry::get($moduleKey))
                @if($module)
                    <a class="{{ request('module') === $moduleKey ? 'active' : '' }}" href="{{ route('admin.modules.index', ['module' => $moduleKey]) }}"><i class="{{ $moduleIcons[$moduleKey] }}"></i> {{ $moduleNavLabels[$moduleKey] ?? $module['title'] }}</a>
                @endif
            @endforeach
            @if(Route::has('admin.visual-editor.index'))
                <a class="{{ (request()->routeIs('admin.page-builder.*') || request()->routeIs('pagebuilder.*')) ? 'active' : '' }}" href="{{ route('pagebuilder.dashboard') }}"><i class="ti ti-layout-grid-add"></i> Səhifə redaktoru</a>
            @endif
            @if(Route::has('admin.media.index'))
                <a class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}"><i class="ti ti-folder-open"></i> Media kitabxanası</a>
            @endif
            @foreach(['ads', 'partners'] as $moduleKey)
                @php($module = \App\Support\Admin\ContentModuleRegistry::get($moduleKey))
                @if($module)
                    <a class="{{ request('module') === $moduleKey ? 'active' : '' }}" href="{{ route('admin.modules.index', ['module' => $moduleKey]) }}"><i class="{{ $moduleIcons[$moduleKey] }}"></i> {{ $moduleNavLabels[$moduleKey] ?? $module['title'] }}</a>
                @endif
            @endforeach

            <div class="nav-title">Məqalə idarəsi</div>
            @foreach(['categories', 'articles'] as $moduleKey)
                @php($module = \App\Support\Admin\ContentModuleRegistry::get($moduleKey))
                @if($module)
                    <a class="{{ request('module') === $moduleKey ? 'active' : '' }}" href="{{ route('admin.modules.index', ['module' => $moduleKey]) }}"><i class="{{ $moduleIcons[$moduleKey] }}"></i> {{ $moduleNavLabels[$moduleKey] ?? $module['title'] }}</a>
                @endif
            @endforeach

            <div class="nav-title">Təlim və arxiv</div>
            @foreach(['trainings', 'gallery', 'certificates_manage'] as $moduleKey)
                @php($module = \App\Support\Admin\ContentModuleRegistry::get($moduleKey))
                @if($module)
                    <a class="{{ request('module') === $moduleKey ? 'active' : '' }}" href="{{ route('admin.modules.index', ['module' => $moduleKey]) }}"><i class="{{ $moduleIcons[$moduleKey] }}"></i> {{ $moduleNavLabels[$moduleKey] ?? $module['title'] }}</a>
                @endif
            @endforeach
            <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="ti ti-users"></i> İstifadəçilər</a>
            <a class="{{ request()->routeIs('admin.contact.*') ? 'active' : '' }}" href="{{ route('admin.contact.index') }}"><i class="ti ti-mail-opened"></i> Əlaqə mesajları</a>
            <a class="{{ request()->routeIs('admin.logs.*') ? 'active' : '' }}" href="{{ route('admin.logs.index') }}"><i class="ti ti-history"></i> Əməliyyat logları</a>

            <div class="nav-title">Keçidlər</div>
            <a href="{{ url('/') }}" target="_blank"><i class="ti ti-external-link"></i> Sayta bax</a>
            <a href="{{ route('admin.logout') }}"><i class="ti ti-logout-2"></i> Çıxış</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-top">
            <div class="top-title">
                <div class="breadcrumbs"><span>Alturamedix</span><i class="ti ti-chevron-right"></i><span>@yield('page_title', 'İdarə paneli')</span></div>
                <h1>@yield('page_title', 'İdarə paneli')</h1>
            </div>
            <div class="top-actions">
                @if($adminLanguages->count() > 1)
                    <div class="admin-language" role="group" aria-label="Admin dili">
                        <i class="ti ti-language"></i>
                        @foreach($adminLanguages as $language)
                            <form method="post" action="{{ route('admin.language.switch') }}">
                                @csrf
                                <button type="submit" name="lang_code" value="{{ $language->code }}" class="{{ $adminSelectedLanguage === $language->code ? 'active' : '' }}" aria-pressed="{{ $adminSelectedLanguage === $language->code ? 'true' : 'false' }}">{{ strtoupper($language->code) }}</button>
                            </form>
                        @endforeach
                    </div>
                @endif
                @if($admin)
                    <div class="admin-profile">
                        <span class="admin-avatar">{{ $adminInitial }}</span>
                        <span>
                            <span class="admin-name">{{ $admin->full_name }}</span>
                            <span class="admin-role">Administrator</span>
                        </span>
                    </div>
                @endif
            </div>
        </div>
        <div class="admin-content">
            @yield('content')
        </div>
    </main>
</div>
<script>
    (() => {
        const layout = document.querySelector('[data-admin-layout]');
        const toggle = document.querySelector('[data-sidebar-toggle]');
        if (!layout || !toggle) return;

        const storageKey = 'alturamedix.admin.sidebarCollapsed';
        const setCollapsed = (collapsed) => {
            layout.classList.toggle('sidebar-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.setAttribute('aria-label', collapsed ? 'Sidebar menyusunu aç' : 'Sidebar menyusunu bağla');
        };

        setCollapsed(localStorage.getItem(storageKey) === '1');
        toggle.addEventListener('click', () => {
            const collapsed = !layout.classList.contains('sidebar-collapsed');
            setCollapsed(collapsed);
            localStorage.setItem(storageKey, collapsed ? '1' : '0');
        });
    })();
</script>
@stack('scripts')
</body>
</html>
