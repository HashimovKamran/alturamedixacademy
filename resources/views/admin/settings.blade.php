@extends('layouts.admin')
@section('title', 'Sayt ayarları')
@section('page_title', 'Sayt ayarları')
@php
    $logoValues = $logoSettings ?? $settings;
    $legacyLogo = trim((string)($logoValues['logo_image'] ?? ''));
    $headerLogo = trim((string)($logoValues['header_logo_image'] ?? '')) ?: $legacyLogo;
    $heroLogo = trim((string)($logoValues['hero_logo_image'] ?? '')) ?: $legacyLogo;
    $mapEnabled = ($settings['home_map_enabled'] ?? '0') === '1';
@endphp
@push('styles')
<style>
.settings-page{display:grid;gap:18px}
.settings-tabs{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:8px;border:1px solid var(--admin-line-2);border-radius:18px;background:#eef8f6}
.settings-tab{display:flex;align-items:center;gap:10px;min-width:0;border:1px solid transparent;border-radius:14px;background:transparent;padding:12px;text-align:left;color:#63747a;font:inherit;font-weight:850;cursor:pointer;transition:background-color .16s ease,border-color .16s ease,box-shadow .16s ease,color .16s ease}
.settings-tab i{width:34px;height:34px;border-radius:12px;display:grid;place-items:center;background:#f8fbfa;color:#718084;font-size:19px;flex:0 0 auto}
.settings-tab span{display:block;min-width:0}
.settings-tab strong{display:block;color:inherit;font-size:13px;font-weight:900;line-height:1.2}
.settings-tab small{display:block;margin-top:3px;color:var(--admin-muted);font-size:11px;font-weight:750;line-height:1.25}
.settings-tab.is-active{background:#fff;border-color:var(--admin-line);color:#111;box-shadow:0 12px 28px rgba(61,125,131,.08)}
.settings-tab.is-active i{background:rgba(255,209,102,.58);color:#111}
.settings-toast-wrap{position:fixed;top:18px;left:50%;z-index:180;width:max-content;max-width:calc(100vw - 32px);transform:translateX(-50%);pointer-events:none}
.settings-toast{display:flex;align-items:center;justify-content:center;gap:10px;padding:13px 16px;border-radius:15px;background:#fff;border:1px solid var(--admin-line);box-shadow:0 22px 56px rgba(15,23,42,.18);font-size:13px;font-weight:850;color:#111;line-height:1.45;text-align:center;animation:settingsToastDrop .28s ease-out both}
.settings-toast i{width:24px;height:24px;border-radius:999px;display:grid;place-items:center;background:#eaf9ef;color:#1f8f4d;font-size:16px}
.settings-toast-error i{background:#fff1f2;color:#c94c5a}
.settings-toast.is-hiding{animation:settingsToastHide .2s ease-in both}
.settings-section{background:#fff;border:1px solid var(--admin-line-2);border-radius:18px;padding:20px;box-shadow:0 14px 38px rgba(61,125,131,.055)}
.settings-section[data-settings-panel]{display:none}
.settings-section[data-settings-panel].is-active{display:block}
.settings-section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px}
.settings-title{display:flex;align-items:flex-start;gap:12px;min-width:0}
.settings-icon{width:38px;height:38px;border-radius:13px;background:#f3faf8;border:1px solid var(--admin-line-2);display:grid;place-items:center;color:#647a80;font-size:20px;flex:0 0 auto}
.settings-section h2{margin:0;color:#111;font-size:20px;font-weight:900;letter-spacing:-.02em}
.settings-section p{margin:5px 0 0;color:var(--admin-muted);font-size:13px;font-weight:750;line-height:1.55}
.settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.settings-grid .full{grid-column:1/-1}
.settings-field label,.logo-upload-card label{display:flex;align-items:center;gap:7px;margin:0 0 7px;color:#202326;font-size:12px;font-weight:900}
.settings-field label i,.logo-upload-card label i{font-size:16px;color:#718084}
.settings-field input,.settings-field textarea{width:100%;border:1px solid var(--admin-line);border-radius:13px;background:#fff;padding:11px 13px;font:inherit;font-size:13px;font-weight:750;outline:0;transition:border-color .16s ease,box-shadow .16s ease,background-color .16s ease}
.settings-field input:focus,.settings-field textarea:focus{border-color:#9fd7d0;box-shadow:0 0 0 4px rgba(117,214,200,.16)}
.settings-field textarea{min-height:116px;resize:vertical;line-height:1.55}
.settings-help{margin-top:7px;color:var(--admin-muted);font-size:12px;font-weight:700;line-height:1.45}
.logo-upload-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.logo-upload-card{border:1px solid var(--admin-line);border-radius:16px;padding:15px;background:linear-gradient(135deg,#fff 0%,#f4fbfa 100%)}
.logo-upload-card input[type=file]{width:100%;border:1px dashed #c9dfdc;border-radius:13px;background:#fbfefd;padding:12px;font:inherit;font-size:12px;font-weight:800;color:#63747a}
.logo-upload-card input[type=file]::file-selector-button{border:0;border-radius:10px;background:#ff7a1a;color:#fff;padding:8px 11px;margin-right:10px;font:inherit;font-size:12px;font-weight:900;cursor:pointer}
.logo-format-badge{display:inline-flex;align-items:center;gap:6px;margin:0 0 12px;padding:7px 10px;border-radius:999px;background:#fff3e8;color:#a04d00;font-size:12px;font-weight:900}
.logo-format-badge i{font-size:16px}
.logo-preview{margin-top:13px;min-height:82px;border-radius:14px;background:#081f31;display:flex;align-items:center;justify-content:center;padding:14px;border:1px solid rgba(8,31,49,.12)}
.logo-preview.light{background:#f6fbfa}
.logo-preview img{max-width:220px;max-height:64px;object-fit:contain}
.logo-empty{margin-top:13px;min-height:82px;border-radius:14px;border:1px dashed #c9dfdc;background:#fbfefd;display:grid;place-items:center;color:#7c9297;font-size:12px;font-weight:850}
.settings-switch{display:inline-flex;align-items:center;gap:10px;margin:0;padding:7px 12px 7px 7px;border:1px solid var(--admin-line);border-radius:999px;background:#fff;white-space:nowrap;cursor:pointer}
.settings-switch input{position:absolute;opacity:0;pointer-events:none}
.settings-switch-track{width:42px;height:24px;border-radius:999px;background:#dce9e6;position:relative;transition:background .18s ease}
.settings-switch-track::after{content:"";position:absolute;width:18px;height:18px;top:3px;left:3px;border-radius:50%;background:#fff;box-shadow:0 2px 8px rgba(15,23,42,.18);transition:transform .18s ease}
.settings-switch input:checked + .settings-switch-track{background:#ff7a1a}
.settings-switch input:checked + .settings-switch-track::after{transform:translateX(18px)}
.settings-switch strong{font-size:12px;font-weight:900;color:#111}
.settings-savebar{position:sticky;bottom:0;z-index:15;display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:18px;padding:14px 16px;background:rgba(248,251,250,.92);border:1px solid var(--admin-line-2);border-radius:16px;box-shadow:0 -10px 34px rgba(61,125,131,.08);backdrop-filter:blur(12px)}
.settings-savebar span{color:var(--admin-muted);font-size:13px;font-weight:800}
.settings-savebar .btn{background:#ff7a1a;color:#fff;box-shadow:0 14px 26px rgba(255,122,26,.24)}
@keyframes settingsToastDrop{from{opacity:0;transform:translateY(-18px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes settingsToastHide{to{opacity:0;transform:translateY(-14px) scale(.98)}}
@media(max-width:900px){.settings-tabs{grid-template-columns:1fr}.settings-section-head,.settings-savebar{align-items:stretch;flex-direction:column}.settings-grid,.logo-upload-grid{grid-template-columns:1fr}.settings-savebar .btn{width:100%}}
</style>
@endpush
@section('content')
@if(session('status') || $errors->any())
    <div class="settings-toast-wrap">
        @if(session('status'))
            <div class="settings-toast" role="status" data-settings-toast>
                <i class="ti ti-check"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="settings-toast settings-toast-error" role="alert" data-settings-toast>
                <i class="ti ti-alert-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
    </div>
@endif

<form class="settings-page" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">

    <div class="settings-tabs" role="tablist" aria-label="Sayt ayarları bölmələri">
        <button class="settings-tab is-active" type="button" role="tab" aria-selected="true" data-settings-tab="logos">
            <i class="ti ti-photo-up"></i>
            <span><strong>Loqolar</strong><small>Yalnız SVG faylları</small></span>
        </button>
        <button class="settings-tab" type="button" role="tab" aria-selected="false" data-settings-tab="contact">
            <i class="ti ti-address-book"></i>
            <span><strong>Əlaqə</strong><small>Telefon, email və ünvan</small></span>
        </button>
        <button class="settings-tab" type="button" role="tab" aria-selected="false" data-settings-tab="map">
            <i class="ti ti-map-2"></i>
            <span><strong>Google Map</strong><small>Xəritə və iframe kodu</small></span>
        </button>
        <button class="settings-tab" type="button" role="tab" aria-selected="false" data-settings-tab="content">
            <i class="ti ti-adjustments"></i>
            <span><strong>Məzmun</strong><small>Brend və qlobal mətnlər</small></span>
        </button>
    </div>

    <section class="settings-section is-active" data-settings-panel="logos" role="tabpanel">
        <div class="settings-section-head">
            <div class="settings-title">
                <span class="settings-icon"><i class="ti ti-photo-up"></i></span>
                <div>
                    <h2>Loqolar</h2>
                    <p>Header/footer və hero loqoları dildən asılı deyil, bütün dillərdə eyni istifadə olunur.</p>
                </div>
            </div>
        </div>
        <div class="logo-format-badge"><i class="ti ti-file-type-svg"></i> Yalnız .svg faylı qəbul olunur</div>
        <div class="logo-upload-grid">
            <div class="logo-upload-card">
                <label for="header_logo_image"><i class="ti ti-layout-navbar"></i> Header / footer loqosu</label>
                <input id="header_logo_image" type="file" name="header_logo_image" accept=".svg,image/svg+xml">
                <div class="settings-help">Ağ və ya açıq rəngli SVG loqo tünd header/footer fonunda daha düzgün görünür. Maksimum 2 MB.</div>
                @if($headerLogo !== '')
                    <div class="logo-preview"><img src="{{ asset(ltrim($headerLogo, '/')) }}" alt=""></div>
                @else
                    <div class="logo-empty">Loqo yüklənməyib</div>
                @endif
            </div>
            <div class="logo-upload-card">
                <label for="hero_logo_image"><i class="ti ti-photo"></i> Hero loqosu</label>
                <input id="hero_logo_image" type="file" name="hero_logo_image" accept=".svg,image/svg+xml">
                <div class="settings-help">Göy və ya tünd rəngli SVG loqo açıq hero fonunda daha oxunaqlı olur. Maksimum 2 MB.</div>
                @if($heroLogo !== '')
                    <div class="logo-preview light"><img src="{{ asset(ltrim($heroLogo, '/')) }}" alt=""></div>
                @else
                    <div class="logo-empty">Loqo yüklənməyib</div>
                @endif
            </div>
        </div>
    </section>

    <section class="settings-section" data-settings-panel="content" role="tabpanel">
        <div class="settings-section-head"><div class="settings-title"><span class="settings-icon"><i class="ti ti-adjustments"></i></span><div><h2>Qlobal məzmun ayarları</h2><p>Header, footer, əsas səhifə və autentifikasiya mətnləri seçilmiş dil üçün idarə olunur.</p></div></div></div>
        @foreach($groups as $groupTitle => $items)
            @continue(in_array($groupTitle, ['Əlaqə', 'Google Map'], true))
            <h3>{{ $groupTitle }}</h3>
            <div class="settings-grid" style="margin-bottom:22px">
                @foreach($items as $key => $label)
                    <div class="settings-field {{ str_contains($key, 'text') || str_contains($key, 'description') || str_contains($key, 'about') ? 'full' : '' }}">
                        <label for="{{ $key }}">{{ $label }}</label>
                        @if(str_contains($key, 'text') || str_contains($key, 'description') || str_contains($key, 'about'))
                            <textarea id="{{ $key }}" name="{{ $key }}">{{ $settings[$key] ?? '' }}</textarea>
                        @else
                            <input id="{{ $key }}" name="{{ $key }}" value="{{ $settings[$key] ?? '' }}">
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </section>

    <section class="settings-section" data-settings-panel="contact" role="tabpanel">
        <div class="settings-section-head">
            <div class="settings-title">
                <span class="settings-icon"><i class="ti ti-address-book"></i></span>
                <div>
                    <h2>Əlaqə məlumatları</h2>
                    <p>Saytın əlaqə səhifəsində və footer-də göstərilən əsas əlaqə məlumatları.</p>
                </div>
            </div>
        </div>
        <div class="settings-grid">
            <div class="settings-field">
                <label for="contact_phone"><i class="ti ti-phone"></i> Telefon</label>
                <input id="contact_phone" type="tel" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+994 50 123 45 67">
            </div>
            <div class="settings-field">
                <label for="contact_email"><i class="ti ti-mail"></i> Email</label>
                <input id="contact_email" type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" placeholder="info@example.az">
            </div>
            <div class="settings-field full">
                <label for="contact_address"><i class="ti ti-map-pin"></i> Ünvan</label>
                <textarea id="contact_address" name="contact_address" placeholder="Ünvanı daxil edin">{{ $settings['contact_address'] ?? '' }}</textarea>
            </div>
        </div>
    </section>

    <section class="settings-section" data-settings-panel="map" role="tabpanel">
        <div class="settings-section-head">
            <div class="settings-title">
                <span class="settings-icon"><i class="ti ti-map-2"></i></span>
                <div>
                    <h2>Google Map</h2>
                    <p>Əlaqə səhifəsində göstərilən xəritənin başlığı, iframe kodu və hündürlüyü.</p>
                </div>
            </div>
            <label class="settings-switch">
                <input type="checkbox" name="home_map_enabled" value="1" @checked($mapEnabled)>
                <span class="settings-switch-track" aria-hidden="true"></span>
                <strong>Xəritə aktiv olsun</strong>
            </label>
        </div>
        <div class="settings-grid">
            <div class="settings-field">
                <label for="home_map_title"><i class="ti ti-heading"></i> Xəritə başlığı</label>
                <input id="home_map_title" type="text" name="home_map_title" value="{{ $settings['home_map_title'] ?? '' }}" placeholder="Ünvan xəritəsi">
            </div>
            <div class="settings-field">
                <label for="home_map_height"><i class="ti ti-arrows-vertical"></i> Xəritə hündürlüyü</label>
                <input id="home_map_height" type="number" min="240" max="900" step="10" name="home_map_height" value="{{ $settings['home_map_height'] ?? '' }}" placeholder="420">
                <div class="settings-help">Piksel ilə daxil edin. Məsələn: 420.</div>
            </div>
            <div class="settings-field full">
                <label for="home_map_subtitle"><i class="ti ti-text-caption"></i> Xəritə alt mətni</label>
                <input id="home_map_subtitle" type="text" name="home_map_subtitle" value="{{ $settings['home_map_subtitle'] ?? '' }}" placeholder="Bizi xəritədə tapın">
            </div>
            <div class="settings-field full">
                <label for="home_map_embed"><i class="ti ti-code"></i> Google Map iframe</label>
                <textarea id="home_map_embed" name="home_map_embed" placeholder="<iframe ...></iframe>">{{ $settings['home_map_embed'] ?? '' }}</textarea>
                <div class="settings-help">Google Maps-dən götürülən embed iframe kodunu tam formada əlavə edin.</div>
            </div>
        </div>
    </section>

    <div class="settings-savebar">
        <span>Loqolar bütün dillər üçün, əlaqə və xəritə məlumatları seçilmiş admin dili üçün yadda saxlanılacaq.</span>
        <button class="btn" type="submit"><i class="ti ti-device-floppy"></i> Yadda saxla</button>
    </div>
</form>

<script>
(() => {
    document.querySelectorAll('[data-settings-toast]').forEach((toast) => {
        window.setTimeout(() => {
            toast.classList.add('is-hiding');
            window.setTimeout(() => toast.remove(), 220);
        }, 4200);
    });

    const tabs = [...document.querySelectorAll('[data-settings-tab]')];
    const panels = [...document.querySelectorAll('[data-settings-panel]')];
    const storageKey = 'alturamedix.settings.activeTab';

    function setActiveTab(key) {
        if (!panels.some((panel) => panel.dataset.settingsPanel === key)) {
            key = 'logos';
        }

        tabs.forEach((tab) => {
            const isActive = tab.dataset.settingsTab === key;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.settingsPanel === key);
        });

        localStorage.setItem(storageKey, key);
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => setActiveTab(tab.dataset.settingsTab));
    });

    setActiveTab(localStorage.getItem(storageKey) || 'logos');
})();
</script>
@endsection
