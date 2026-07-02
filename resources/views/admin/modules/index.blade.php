@extends('layouts.admin')

@section('title', $module['title'])
@section('page_title', $module['title'])

@push('styles')
<style>
.lang-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 18px}.lang-tabs a{padding:8px 11px;border:1px solid var(--admin-line);border-radius:999px;background:#fff;color:#2f3439;text-decoration:none;font-size:12px;font-weight:900}.lang-tabs a.active{background:var(--admin-accent);border-color:var(--admin-accent);color:#111}.filter-grid{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end}.module-filter-card{padding:16px 18px}.module-filter-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.module-filter-head h2{margin:0;font-size:17px}.module-filter-head span{font-size:12px;font-weight:800;color:var(--admin-muted)}.filter-pills{display:flex;gap:8px;flex-wrap:wrap}.filter-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--admin-line);border-radius:999px;background:#fff;color:#2d3236;padding:9px 12px;font-size:12px;font-weight:900}.filter-pill.is-active{background:#fff6ed;border-color:#ffb36f;color:#d85d00}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.form-row label{display:block;font-weight:900;margin-bottom:6px}.form-row input,.form-row textarea,.form-row select{width:100%;border:1px solid var(--admin-line);border-radius:11px;padding:10px 12px;font:inherit;font-size:13px}.form-row textarea{min-height:116px}.form-row.full{grid-column:1/-1}.check{display:flex;align-items:center;gap:10px}.check input{width:auto}.module-table{width:100%;border-collapse:separate;border-spacing:0}.module-table th,.module-table td{padding:12px;border-bottom:1px solid var(--admin-line-2);text-align:left;vertical-align:middle}.module-table th{background:#f3faf8;color:#6e858a;font-size:11px;text-transform:none}.module-table tbody tr:hover td{background:#f8fdfb}.preview-img{width:70px;height:48px;object-fit:cover;border-radius:9px;background:var(--admin-soft);border:1px solid var(--admin-line)}.actions{display:flex;gap:7px;flex-wrap:wrap}.actions .btn{padding:8px 10px;font-size:12px}.parent-badge{display:inline-block;border-radius:999px;padding:4px 9px;background:var(--admin-mint-soft);color:#287f73;border:1px solid var(--admin-line-2);font-weight:850;font-size:11px}.module-toolbar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:16px}.module-modal{position:fixed;inset:0;z-index:120;display:none;background:rgba(15,23,42,.34);backdrop-filter:blur(8px);padding:24px;overflow:auto;margin:0;border:0;border-radius:0;box-shadow:none}.module-modal.is-open{display:grid;place-items:start center}.module-modal-panel{width:min(760px,100%);background:#fff;border:1px solid var(--admin-line);border-radius:20px;padding:20px;box-shadow:0 28px 90px rgba(17,24,39,.18)}.module-modal-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:18px}.module-modal-head h2{margin:0}.module-modal-close{width:38px;height:38px;border-radius:12px;border:1px solid var(--admin-line);display:grid;place-items:center;background:#fff;color:#111}.drag-cell{width:44px}.drag-handle{width:34px;height:34px;border:1px solid var(--admin-line);border-radius:11px;background:#fff;color:#718084;display:grid;place-items:center;cursor:grab}.drag-handle:active{cursor:grabbing}.module-table tr.dragging{opacity:.45}.module-table tr.drag-over td{background:#fff9e8}@media(max-width:900px){.form-grid,.filter-grid{grid-template-columns:1fr}.module-table{display:block;overflow:auto}.module-modal{padding:14px}}
</style>
<style>
.status-icon{width:32px;height:32px;border-radius:10px;display:inline-grid;place-items:center;border:1px solid var(--admin-line);background:#f7fbfa;color:#718084;font-size:17px}
.status-icon.is-on{background:#eaf9ef;border-color:#c7efd3;color:#1f8f4d}
.status-icon.is-off{background:#fff1f2;border-color:#ffd0d5;color:#c94c5a}
.target-choice{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.target-option{display:block;margin:0;cursor:pointer}
.target-option input{position:absolute;opacity:0;pointer-events:none;width:1px!important;height:1px!important;margin:0!important;padding:0!important}
.target-option-card{min-height:74px;display:flex;align-items:center;gap:12px;padding:13px 14px;border:1px solid var(--admin-line);border-radius:15px;background:#f8fbfa;transition:border-color .16s ease,background-color .16s ease,box-shadow .16s ease}
.target-option-card i{width:38px;height:38px;border-radius:13px;display:grid;place-items:center;border:1px solid var(--admin-line);background:#fff;color:#66797e;font-size:19px;flex:0 0 auto;transition:inherit}
.target-option-copy{display:grid;gap:3px;min-width:0}
.target-option-copy strong{font-size:13px;font-weight:900;color:#111}
.target-option-copy small{font-size:12px;font-weight:760;color:var(--admin-muted);line-height:1.35}
.target-option input:checked + .target-option-card{border-color:#ffb36f;background:#fff6ed;box-shadow:0 12px 26px rgba(255,122,26,.11)}
.target-option input:checked + .target-option-card i{border-color:#ff7a1a;background:#ff7a1a;color:#fff}
.target-option input:focus-visible + .target-option-card{outline:3px solid rgba(255,122,26,.22);outline-offset:2px}
.position-choice .target-option-card{min-height:86px}
.menu-status-row{align-self:start;padding-bottom:0!important;margin-top:-4px}
.category-visual-picker{display:grid;gap:14px}
.category-visual-mode{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.category-visual-panel{display:none}
.category-visual-panel.is-active{display:grid;gap:12px}
.category-icon-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(122px,1fr));gap:8px;max-height:248px;overflow:auto;padding:2px}
.category-icon-option{display:block;margin:0;cursor:pointer}
.category-icon-option input{position:absolute;opacity:0;pointer-events:none;width:1px!important;height:1px!important;margin:0!important;padding:0!important}
.category-icon-card{min-height:86px;display:grid;place-items:center;text-align:center;gap:7px;padding:11px;border:1px solid var(--admin-line);border-radius:14px;background:#f8fbfa;transition:border-color .16s ease,background-color .16s ease,box-shadow .16s ease}
.category-icon-card i{font-size:24px;color:#66797e}
.category-icon-card span{font-size:11px;font-weight:850;color:#243035;line-height:1.25}
.category-icon-option input:checked + .category-icon-card{border-color:#ffb36f;background:#fff6ed;box-shadow:0 10px 22px rgba(255,122,26,.1)}
.category-icon-option input:checked + .category-icon-card i{color:#ff7a1a}
.category-visual-cell{display:flex;align-items:center;gap:10px}
.category-visual-cell i{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;background:#f7fbfa;border:1px solid var(--admin-line);font-size:18px;color:#111}
.category-visual-cell .preview-img{width:48px;height:48px}
.category-visual-cell span{font-size:12px;font-weight:800;color:var(--admin-muted)}
@media(max-width:720px){.target-choice{grid-template-columns:1fr}}
</style>
<style>
.module-toolbar .btn-primary{background:#ff7a1a;color:#fff;box-shadow:0 14px 26px rgba(255,122,26,.26)}
body.module-modal-open{overflow:hidden}
.module-modal{width:auto;max-width:none;overflow-x:hidden;overscroll-behavior:contain}
.module-modal.is-open{place-items:center}
.module-modal-panel{width:min(720px,calc(100vw - 32px));padding:28px;border-radius:18px}
.module-modal-head{margin-bottom:24px}
.module-modal .form-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.module-modal .form-row{display:flex;flex-direction:column;min-width:0}
.module-modal .form-row label{margin-bottom:8px}
.module-modal .form-row input,.module-modal .form-row select{height:52px;border-radius:13px}
.module-modal .check-row{justify-content:flex-start;align-self:end;padding-bottom:3px}
.module-modal .check{height:52px;margin:0;padding:0 14px;border:1px solid var(--admin-line);border-radius:13px;background:#f8fbfa}
.module-modal .check input{width:18px;height:18px;accent-color:#ff7a1a}
.module-modal .check.menu-switch{position:relative;display:inline-flex;align-items:center;width:100%;height:auto;min-height:54px;margin:0;padding:7px 14px 7px 8px;border-radius:16px;background:#fff;gap:11px}
.module-modal .check.menu-switch input{position:absolute;opacity:0;pointer-events:none}
.menu-switch-copy{display:grid;gap:2px;min-width:0}
.menu-switch-copy strong{font-size:13px;font-weight:900;color:#202326;line-height:1.25}
.menu-switch-copy small{font-size:11px;font-weight:850;color:var(--admin-muted);line-height:1.25}
.menu-switch-track{display:block;width:42px;height:24px;border-radius:999px;background:#dce9e6;position:relative;flex:0 0 42px;transition:background .18s ease}
.menu-switch-track::after{content:"";position:absolute;width:18px;height:18px;top:3px;left:3px;border-radius:999px;background:#fff;box-shadow:0 2px 8px rgba(15,23,42,.18);transition:transform .18s ease}
.menu-switch input:checked + .menu-switch-track{background:#ff7a1a}
.menu-switch input:checked + .menu-switch-track::after{transform:translateX(18px)}
.module-modal-actions{display:flex;align-items:center;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid var(--admin-line-2)}
.module-modal .module-save-btn{background:#ff7a1a;color:#fff;box-shadow:0 14px 26px rgba(255,122,26,.26)}
.module-modal .module-save-btn:hover{box-shadow:0 16px 30px rgba(255,122,26,.32)}
.module-toast-wrap{position:fixed;top:18px;left:50%;z-index:180;display:grid;justify-items:center;gap:10px;width:max-content;max-width:calc(100vw - 32px);transform:translateX(-50%);pointer-events:none}
.module-toast{display:flex;align-items:center;justify-content:center;gap:10px;padding:13px 16px;border-radius:15px;background:#fff;border:1px solid var(--admin-line);box-shadow:0 22px 56px rgba(15,23,42,.18);font-size:13px;font-weight:850;color:#111;line-height:1.45;text-align:center;animation:moduleToastDrop .28s ease-out both;pointer-events:auto}
.module-toast i{width:24px;height:24px;border-radius:999px;display:grid;place-items:center;flex:0 0 auto;font-size:16px}
.module-toast-success i{background:#eaf9ef;color:#1f8f4d}
.module-toast-error i{background:#fff1f2;color:#c94c5a}
.module-toast-close{margin-left:auto;width:26px;height:26px;border:0;border-radius:9px;background:#f4faf8;color:#4d5d62;display:grid;place-items:center;cursor:pointer;font-size:16px}
.module-toast.is-hiding{animation:moduleToastHide .2s ease-in both}
.module-file-upload{display:grid;gap:10px}
.module-file-input{position:absolute;width:1px!important;height:1px!important;opacity:0;pointer-events:none}
.module-file-drop{display:flex!important;align-items:center;gap:12px;margin:0!important;padding:14px!important;border:1px dashed #c6dfdc;border-radius:15px;background:#f8fdfb;cursor:pointer;transition:border-color .16s ease,background-color .16s ease,box-shadow .16s ease}
.module-file-drop:hover{border-color:#ffb36f;background:#fffaf5;box-shadow:0 10px 24px rgba(255,122,26,.08)}
.module-file-icon{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:#fff3e8;color:#ff7a1a;font-size:22px;flex:0 0 auto}
.module-file-copy{display:grid;gap:3px;min-width:0}
.module-file-copy strong{font-size:13px;font-weight:900;color:#111}
.module-file-copy small{font-size:12px;font-weight:750;color:var(--admin-muted);white-space:normal}
.module-file-current{display:flex;align-items:center;gap:10px;padding:9px;border:1px solid var(--admin-line-2);border-radius:13px;background:#fff}
.module-file-current .preview-img{width:74px;height:52px}
.module-file-current span{font-size:12px;font-weight:800;color:var(--admin-muted);word-break:break-all}
.actions .btn.action-icon{width:36px;height:36px;padding:0;border-radius:11px}
.actions .btn.action-icon i{font-size:18px}
.module-table th:last-child,.module-table td:last-child{width:132px;min-width:132px}
.actions{flex-wrap:nowrap;align-items:center}
.actions form{display:inline-flex;margin:0;flex:0 0 auto}
.filter-grid-articles{grid-template-columns:minmax(260px,2fr) minmax(180px,1fr)}
.filter-grid-certificates{grid-template-columns:minmax(260px,2fr) minmax(190px,1fr)}
.article-category-choice .target-option-card i,.article-category-choice .target-option-card img{width:38px;height:38px;border-radius:13px;display:grid;place-items:center;border:1px solid var(--admin-line);background:#fff;color:#66797e;font-size:18px;flex:0 0 auto;object-fit:contain;padding:7px;transition:inherit}
.article-category-choice .target-option-card img{padding:5px}
.article-notify-option{position:relative;display:flex!important;align-items:center;gap:12px;margin:0!important;padding:13px 14px!important;border:1px solid var(--admin-line);border-radius:16px;background:#f8fbfa;cursor:pointer;transition:border-color .16s ease,background-color .16s ease,box-shadow .16s ease}
.article-notify-option input{position:absolute;opacity:0;pointer-events:none;width:1px!important;height:1px!important;margin:0!important;padding:0!important}
.article-notify-icon{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:#fff3e8;color:#ff7a1a;font-size:21px;flex:0 0 auto}
.article-notify-copy{display:grid;gap:3px;min-width:0}
.article-notify-copy strong{font-size:13px;font-weight:900;color:#111;line-height:1.25}
.article-notify-copy small{font-size:12px;font-weight:760;color:var(--admin-muted);line-height:1.35}
.article-notify-check{margin-left:auto;width:30px;height:30px;border-radius:11px;border:1px solid var(--admin-line);background:#fff;display:grid;place-items:center;color:transparent;flex:0 0 auto;transition:inherit}
.article-notify-check::before{content:"\2713";font-size:16px;font-weight:900}
.article-notify-option input:checked ~ .article-notify-check{background:#ff7a1a;border-color:#ff7a1a;color:#fff}
.article-notify-option input:checked ~ .article-notify-icon{background:#ff7a1a;color:#fff}
.article-notify-option:has(input:checked){border-color:#ffb36f;background:#fff6ed;box-shadow:0 12px 26px rgba(255,122,26,.1)}
.cert-qr-placement{display:grid;gap:12px}
.cert-qr-stage{position:relative;min-height:360px;box-sizing:border-box;border:1px dashed #c6dfdc;border-radius:18px;background:#f8fdfb center/contain no-repeat;overflow:hidden}
.cert-qr-stage.has-media{min-height:0;aspect-ratio:var(--cert-qr-aspect);background-size:100% 100%;background-repeat:no-repeat;background-position:center}
.cert-qr-pdf-preview{position:absolute;inset:0;z-index:0;display:block;width:100%;height:100%;border:0;background:#fff;pointer-events:none}
.cert-qr-pdf-preview[hidden]{display:none}
.cert-qr-empty{position:absolute;inset:0;display:grid;place-items:center;text-align:center;padding:22px;color:var(--admin-muted);font-size:13px;font-weight:850}
.cert-qr-empty[hidden]{display:none}
.cert-qr-box{position:absolute;z-index:2;left:72%;top:72%;width:16%;box-sizing:border-box;aspect-ratio:1;border:2px solid #ff7a1a;border-radius:10px;background:#fff;box-shadow:0 14px 32px rgba(255,122,26,.18);display:grid;place-items:center;cursor:grab;touch-action:none;outline:0}
.cert-qr-box:active{cursor:grabbing}
.cert-qr-box:focus-visible{box-shadow:0 0 0 4px rgba(255,122,26,.2),0 14px 32px rgba(255,122,26,.18)}
.cert-qr-box img{display:block;width:100%;height:100%;object-fit:contain;border-radius:7px}
.cert-qr-box span{width:88%;height:88%;border-radius:5px;background:repeating-linear-gradient(45deg,#111 0 4px,#fff 4px 8px);display:grid;place-items:center;color:#ff7a1a;font-size:12px;font-weight:950;text-shadow:0 1px #fff}
.cert-qr-resize{position:absolute;right:-10px;bottom:-10px;width:28px;height:28px;border:0;border-radius:10px;background:#ff7a1a;color:#fff;display:grid;place-items:center;cursor:nwse-resize;box-shadow:0 10px 22px rgba(255,122,26,.28)}
.cert-qr-controls{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:12px;align-items:center;padding:12px;border:1px solid var(--admin-line);border-radius:15px;background:#f8fbfa}
.cert-qr-controls input[type=range]{width:100%;accent-color:#ff7a1a}
.cert-qr-controls strong{font-size:13px;font-weight:900;color:#111;white-space:nowrap}
.cert-qr-coordinates{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.cert-qr-coordinate{padding:6px 8px;border:1px solid var(--admin-line);border-radius:9px;background:#fff;color:var(--admin-muted);font-size:11px;font-weight:850;white-space:nowrap}
.cert-qr-help{font-size:12px;font-weight:780;color:var(--admin-muted);line-height:1.5}
.cert-validity{display:grid;gap:13px}
.cert-validity-options{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.cert-validity-options .target-option-card{min-height:92px;align-items:flex-start}
.cert-validity-options .target-option-card i{width:36px;height:36px;border-radius:12px;font-size:18px}
.cert-validity-custom{display:none;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;align-items:end;padding:13px;border:1px solid var(--admin-line);border-radius:15px;background:#f8fbfa}
.cert-validity-custom.is-active{display:grid}
.cert-validity-custom label{margin:0 0 7px!important;font-size:12px}
.cert-validity-custom input{background:#fff}
.cert-validity-summary{display:flex;align-items:center;gap:9px;min-height:46px;padding:10px 12px;border-radius:13px;background:#fff6ed;border:1px solid #ffd1aa;color:#9b4400;font-size:12px;font-weight:850}
.cert-validity-summary i{font-size:18px;color:#ff7a1a}
.cert-revocation{display:grid;gap:12px}
.cert-revocation .menu-switch{width:100%}
.cert-revoke-reason{display:none;padding:13px;border:1px solid #ffd0d5;border-radius:15px;background:#fff7f7}
.cert-revoke-reason.is-active{display:grid;gap:8px}
.cert-revoke-reason label{margin:0!important;color:#9f2635;font-size:12px}
.cert-revoke-reason textarea{background:#fff;border-color:#f0bbc2!important}
.module-results{position:relative}
.module-results.is-loading{opacity:.62;pointer-events:none}
@keyframes moduleToastDrop{from{opacity:0;transform:translateY(-18px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes moduleToastHide{to{opacity:0;transform:translateY(-14px) scale(.98)}}
@media(max-width:900px){.cert-validity-options{grid-template-columns:repeat(2,minmax(0,1fr))}.cert-qr-controls{grid-template-columns:1fr auto}.cert-qr-coordinates{grid-column:1/-1}}
@media(max-width:720px){.module-modal-panel{padding:20px}.module-modal .form-grid{grid-template-columns:1fr}.module-modal-actions{flex-direction:column;align-items:stretch}.module-modal-actions .btn{width:100%}.cert-validity-options,.cert-validity-custom{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    if (in_array($moduleKey, ['pages', 'blocks', 'features'], true)) {
        abort(404);
    }

    $medicalIcons = [
        'fa-solid fa-truck-medical' => 'Təcili yardım',
        'fa-solid fa-user-doctor' => 'Həkim',
        'fa-solid fa-user-nurse' => 'Tibb bacısı',
        'fa-solid fa-stethoscope' => 'Stetoskop',
        'fa-solid fa-heart-pulse' => 'Kardiologiya',
        'fa-solid fa-brain' => 'Nevrologiya',
        'fa-solid fa-lungs' => 'Ağciyər',
        'fa-solid fa-virus' => 'Virus',
        'fa-solid fa-dna' => 'Genetika',
        'fa-solid fa-microscope' => 'Mikroskop',
        'fa-solid fa-vial' => 'Vial',
        'fa-solid fa-vials' => 'Viallar',
        'fa-solid fa-flask-vial' => 'Laboratoriya',
        'fa-solid fa-syringe' => 'İynə',
        'fa-solid fa-pills' => 'Dərman',
        'fa-solid fa-capsules' => 'Kapsul',
        'fa-solid fa-prescription-bottle-medical' => 'Resept',
        'fa-solid fa-kit-medical' => 'Tibb çantası',
        'fa-solid fa-suitcase-medical' => 'Təcili çanta',
        'fa-solid fa-notes-medical' => 'Tibbi qeydlər',
        'fa-solid fa-file-medical' => 'Tibbi sənəd',
        'fa-solid fa-book-medical' => 'Tibbi kitab',
        'fa-solid fa-hospital' => 'Hospital',
        'fa-solid fa-house-medical' => 'Klinika',
        'fa-solid fa-bed-pulse' => 'Reanimasiya',
        'fa-solid fa-x-ray' => 'Rentgen',
        'fa-solid fa-bandage' => 'Sarğı',
        'fa-solid fa-bone' => 'Travma',
        'fa-solid fa-tooth' => 'Diş',
        'fa-solid fa-eye' => 'Göz',
        'fa-solid fa-ear-listen' => 'Qulaq',
        'fa-solid fa-wheelchair' => 'Reabilitasiya',
        'fa-solid fa-hand-holding-medical' => 'Tibbi yardım',
        'fa-solid fa-staff-snake' => 'Tibb simvolu',
        'fa-solid fa-temperature-high' => 'Temperatur',
        'fa-solid fa-person-dots-from-line' => 'Diaqnostika',
    ];
    $modalModules = ['menus', 'sliders', 'stats', 'ads', 'partners', 'categories', 'articles', 'gallery', 'trainings', 'certificates_manage'];
    $isModalModule = in_array($moduleKey, $modalModules, true);
    $isSortableModule = in_array($moduleKey, ['menus', 'sliders', 'stats', 'ads', 'partners', 'categories', 'gallery', 'trainings'], true);
    $isAjaxFilterModule = in_array($moduleKey, ['articles', 'certificates_manage'], true);
    $hasFilter = in_array($moduleKey, ['articles', 'certificates_manage'], true);
    $shouldOpenModal = $isModalModule && ($edit || $errors->any() || request()->boolean('create'));
@endphp
@if(session('status') || $errors->any())
    <div class="module-toast-wrap" data-module-toast-wrap>
        @if(session('status'))
            <div class="module-toast module-toast-success" role="status" data-module-toast>
                <i class="ti ti-check"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="module-toast module-toast-error" role="alert" data-module-toast>
                <i class="ti ti-alert-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
    </div>
@endif

@if($isModalModule)
    <div class="module-toolbar">
        <button class="btn btn-primary" type="button" data-module-modal-open><i class="ti ti-plus"></i> Yeni əlavə et</button>
    </div>
@endif

@if($hasFilter)
<div class="card module-filter-card">
    <div class="module-filter-head">
        <h2>Axtarış və filter</h2>
        <span>Nəticələri sürətli daralt</span>
    </div>
    <form method="get" action="{{ route('admin.modules.index', ['module' => $moduleKey]) }}" data-module-filter-form>
        <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
        @if($moduleKey === 'articles' && ($filters['category_id'] ?? '') !== '')
            <input type="hidden" name="category_id" value="{{ $filters['category_id'] }}">
        @endif
        <div class="filter-grid {{ $moduleKey === 'articles' ? 'filter-grid-articles' : ($moduleKey === 'certificates_manage' ? 'filter-grid-certificates' : '') }}">
            <div><label>Axtarış</label><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $moduleKey === 'certificates_manage' ? 'Sənəd nömrəsi, ad soyad, təlim...' : 'Başlıq, ad, email, slug...' }}" data-filter-debounce @if(in_array($moduleKey, ['articles', 'certificates_manage'], true)) data-filter-min-chars="2" @endif></div>
            @if($moduleKey === 'articles')
                <div>
                    <label>Aktivlik</label>
                    <select name="active" data-filter-auto-submit>
                        <option value="">Hamısı</option>
                        <option value="1" @selected(($filters['active'] ?? '') === '1')>Aktiv</option>
                        <option value="0" @selected(($filters['active'] ?? '') === '0')>Passiv</option>
                    </select>
                </div>
            @endif
            @if($moduleKey === 'certificates_manage')
                <div>
                    <label>Status</label>
                    <select name="status" data-filter-auto-submit>
                        <option value="">Hamısı</option>
                        <option value="valid" @selected(($filters['status'] ?? '') === 'valid')>Keçərlidir</option>
                        <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Müddəti bitib</option>
                        <option value="revoked" @selected(($filters['status'] ?? '') === 'revoked')>Ləğv edilib</option>
                    </select>
                </div>
            @endif
        </div>
        @if($moduleKey === 'articles')
            <div class="filter-pills" style="margin-top:12px">
                <a class="filter-pill {{ ($filters['category_id'] ?? '') === '' ? 'is-active' : '' }}" href="{{ route('admin.modules.index', ['module' => $moduleKey, 'lang_code' => $selectedLanguage] + request()->except(['category_id', 'page'])) }}">Bütün kateqoriyalar</a>
                @foreach($categoryOptions as $optionValue => $label)
                    <a class="filter-pill {{ (string)($filters['category_id'] ?? '') === (string)$optionValue ? 'is-active' : '' }}" href="{{ route('admin.modules.index', ['module' => $moduleKey, 'lang_code' => $selectedLanguage, 'category_id' => $optionValue] + request()->except(['category_id', 'page'])) }}">{{ $label }}</a>
                @endforeach
            </div>
        @endif
    </form>
</div>
@endif

<div class="card {{ $isModalModule ? 'module-modal' : '' }} {{ $shouldOpenModal ? 'is-open' : '' }}" @if($isModalModule) data-module-modal @endif>
    @if($isModalModule)
        <div class="module-modal-panel">
            <div class="module-modal-head">
                <h2>{{ $edit ? 'Redaktə et' : 'Yeni əlavə et' }}</h2>
                <a class="module-modal-close" href="{{ route('admin.modules.index', ['module' => $moduleKey]) }}" data-module-modal-close aria-label="Bağla"><i class="ti ti-x"></i></a>
            </div>
    @else
        <h2>{{ $edit ? 'Redaktə et' : 'Yeni əlavə et' }}</h2>
    @endif
    <form method="post" action="{{ route('admin.modules.store', ['module' => $moduleKey]) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $edit?->id }}">
        @if(isset($module['fields']['lang_code']))
            <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
        @endif
        <div class="form-grid">
            @foreach($module['fields'] as $name => $field)
                @php
                    $type = $field['type'];
                    $value = old($name, $edit?->{$name} ?? ($field['default'] ?? ''));
                    if ($moduleKey === 'menus' && $name === 'target' && $value === '') {
                        $value = '_self';
                    }
                    if ($moduleKey === 'ads' && $name === 'position_key' && $value === '') {
                        $value = 'sidebar';
                    }
                    if ($moduleKey === 'certificates_manage' && $name === 'issue_date' && !$value) {
                        $value = now()->format('Y-m-d');
                    }
                    $isMenuTarget = $moduleKey === 'menus' && $name === 'target';
                    $isAdPosition = $moduleKey === 'ads' && $name === 'position_key';
                    $isAdFullInput = $moduleKey === 'ads' && in_array($name, ['title', 'url'], true);
                    $isPartnerFullInput = $moduleKey === 'partners' && in_array($name, ['title', 'url'], true);
                    $isGalleryTitle = $moduleKey === 'gallery' && $name === 'title';
                    $isMenuCheckbox = $moduleKey === 'menus' && $type === 'checkbox';
                    $isCategoryVisual = $moduleKey === 'categories' && $name === 'icon_class';
                    $isCategoryImage = $moduleKey === 'categories' && $name === 'image_path';
                    $isArticleCategory = $moduleKey === 'articles' && $type === 'category';
                    $isCertificateQr = $moduleKey === 'certificates_manage' && $type === 'qr_position';
                    $isCertificateValidity = $moduleKey === 'certificates_manage' && $type === 'certificate_validity';
                    $isCertificateLifecycle = $moduleKey === 'certificates_manage' && in_array($type, ['checkbox', 'certificate_revocation'], true);
                    $isWide = in_array($type, ['textarea', 'file', 'svg_file', 'medical_icon', 'qr_position', 'certificate_validity', 'certificate_revocation'], true) || $isMenuTarget || $isAdPosition || $isAdFullInput || $isPartnerFullInput || $isGalleryTitle || $isMenuCheckbox || $isCategoryVisual || $isArticleCategory || $isCertificateQr || $isCertificateValidity || $isCertificateLifecycle;
                @endphp
                @continue($type === 'language')
                @continue($isSortableModule && $name === 'sort_order')
                @continue($isCategoryImage)
                <div class="form-row {{ $isWide ? 'full' : '' }} {{ $type === 'checkbox' ? 'check-row' : '' }} {{ $isMenuCheckbox ? 'menu-status-row' : '' }}" data-field-name="{{ $name }}" data-field-type="{{ $type }}">
                    @if($type === 'checkbox')
                        <label class="check {{ $isModalModule ? 'menu-switch' : '' }}">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked((bool)$value)>
                            @if($isModalModule)
                                <span class="menu-switch-track" aria-hidden="true"></span>
                                <span class="menu-switch-copy">
                                    <strong>{{ $field['label'] }}</strong>
                                    <small class="menu-switch-label" data-active-label="Aktiv" data-passive-label="{{ $field['passive_label'] ?? 'Passiv' }}"></small>
                                </span>
                            @else
                                {{ $field['label'] }}
                            @endif
                        </label>
                    @else
                        <label>{{ $field['label'] }}</label>
                        @if($type === 'language')
                            <select name="{{ $name }}">
                                @foreach($languages as $language)
                                    <option value="{{ $language->code }}" @selected((string)$value === $language->code || (!$edit && $selectedLanguage === $language->code))>{{ strtoupper($language->code) }} - {{ $language->title }}</option>
                                @endforeach
                            </select>
                        @elseif($moduleKey === 'menus' && $name === 'target')
                            @php
                                $targetNotes = ['_self' => 'Link cari tabda açılacaq.', '_blank' => 'Link yeni tabda açılacaq.'];
                                $targetIcons = ['_self' => 'ti-window', '_blank' => 'ti-external-link'];
                            @endphp
                            <div class="target-choice" role="radiogroup" aria-label="{{ $field['label'] }}">
                                @foreach($field['options'] as $optionValue => $label)
                                    <label class="target-option">
                                        <input type="radio" name="{{ $name }}" value="{{ $optionValue }}" @checked((string)$value === (string)$optionValue)>
                                        <span class="target-option-card">
                                            <i class="ti {{ $targetIcons[$optionValue] ?? 'ti-link' }}"></i>
                                            <span class="target-option-copy">
                                                <strong>{{ $label }}</strong>
                                                <small>{{ $targetNotes[$optionValue] ?? '' }}</small>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($moduleKey === 'ads' && $name === 'position_key')
                            @php
                                $positionNotes = ['sidebar' => 'Yan panel reklam sahəsində görünəcək.', 'bottom' => 'Səhifənin aşağı reklam sahəsində görünəcək.'];
                                $positionIcons = ['sidebar' => 'ti-layout-sidebar-right', 'bottom' => 'ti-layout-bottombar'];
                            @endphp
                            <div class="target-choice position-choice" role="radiogroup" aria-label="{{ $field['label'] }}">
                                @foreach($field['options'] as $optionValue => $label)
                                    <label class="target-option">
                                        <input type="radio" name="{{ $name }}" value="{{ $optionValue }}" @checked((string)$value === (string)$optionValue)>
                                        <span class="target-option-card">
                                            <i class="ti {{ $positionIcons[$optionValue] ?? 'ti-layout' }}"></i>
                                            <span class="target-option-copy">
                                                <strong>{{ $label }}</strong>
                                                <small>{{ $positionNotes[$optionValue] ?? '' }}</small>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($type === 'medical_icon')
                            @php
                                $categoryImageValue = old('image_path', $edit?->image_path ?? '');
                                $categoryVisualType = old('category_visual_type') ?: (trim((string) $value) !== '' ? 'icon' : (trim((string) $categoryImageValue) !== '' ? 'image' : 'icon'));
                                $iconPanelActive = $categoryVisualType === 'icon';
                                $imagePanelActive = $categoryVisualType === 'image';
                            @endphp
                            <div class="category-visual-picker" data-category-visual>
                                <div class="category-visual-mode" role="radiogroup" aria-label="Vizual tipi">
                                    <label class="target-option">
                                        <input type="radio" name="category_visual_type" value="icon" @checked($iconPanelActive)>
                                        <span class="target-option-card">
                                            <i class="ti ti-icons"></i>
                                            <span class="target-option-copy">
                                                <strong>Icon class</strong>
                                                <small>Hazır tibbi ikonlardan birini seç.</small>
                                            </span>
                                        </span>
                                    </label>
                                    <label class="target-option">
                                        <input type="radio" name="category_visual_type" value="image" @checked($imagePanelActive)>
                                        <span class="target-option-card">
                                            <i class="ti ti-file-type-svg"></i>
                                            <span class="target-option-copy">
                                                <strong>SVG şəkil</strong>
                                                <small>Yalnız .svg faylı yüklə.</small>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <div class="category-visual-panel {{ $iconPanelActive ? 'is-active' : '' }}" data-category-icon-panel>
                                    <div class="category-icon-grid">
                                        @foreach($medicalIcons as $iconClass => $iconLabel)
                                            <label class="category-icon-option">
                                                <input type="radio" name="{{ $name }}" value="{{ $iconClass }}" @checked((string) $value === $iconClass) @disabled(!$iconPanelActive)>
                                                <span class="category-icon-card">
                                                    <i class="{{ $iconClass }}"></i>
                                                    <span>{{ $iconLabel }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="category-visual-panel {{ $imagePanelActive ? 'is-active' : '' }}" data-category-image-panel>
                                    @php
                                        $fileInputId = 'module_file_' . $moduleKey . '_image_path';
                                    @endphp
                                    <div class="module-file-upload">
                                        <input id="{{ $fileInputId }}" class="module-file-input" type="file" name="image_path" accept=".svg,image/svg+xml" data-module-file-input @disabled(!$imagePanelActive)>
                                        <label class="module-file-drop" for="{{ $fileInputId }}">
                                            <span class="module-file-icon"><i class="ti ti-file-type-svg"></i></span>
                                            <span class="module-file-copy">
                                                <strong>SVG seç</strong>
                                                <small data-module-file-name>Yalnız SVG faylı qəbul olunur</small>
                                            </span>
                                        </label>
                                        @if($categoryImageValue)
                                            <div class="module-file-current">
                                                <img class="preview-img" src="{{ asset(ltrim((string)$categoryImageValue, '/')) }}" alt="">
                                                <span>{{ basename((string)$categoryImageValue) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($type === 'certificate_validity')
                            @php
                                $issueDateValue = old('issue_date', $edit?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d'));
                                $expireDateValue = old('expire_date', $edit?->expire_date?->format('Y-m-d') ?? '');
                                $validityPeriod = old('validity_period');

                                if (!$validityPeriod) {
                                    if (!$edit) {
                                        $validityPeriod = '2_years';
                                    } elseif ($expireDateValue === '') {
                                        $validityPeriod = 'lifetime';
                                    } elseif ($issueDateValue !== '') {
                                        $issue = \Illuminate\Support\Carbon::parse($issueDateValue);
                                        $expiry = \Illuminate\Support\Carbon::parse($expireDateValue);
                                        $validityPeriod = $issue->copy()->addYearNoOverflow()->isSameDay($expiry)
                                            ? '1_year'
                                            : ($issue->copy()->addYearsNoOverflow(2)->isSameDay($expiry)
                                                ? '2_years'
                                                : ($issue->copy()->addYearsNoOverflow(3)->isSameDay($expiry) ? '3_years' : 'custom'));
                                    } else {
                                        $validityPeriod = 'custom';
                                    }
                                }

                                $validityOptions = [
                                    '1_year' => ['label' => '1 il', 'note' => 'Verilmə tarixindən 1 il', 'icon' => 'ti-calendar-event'],
                                    '2_years' => ['label' => '2 il', 'note' => 'Verilmə tarixindən 2 il', 'icon' => 'ti-calendar-time'],
                                    '3_years' => ['label' => '3 il', 'note' => 'Verilmə tarixindən 3 il', 'icon' => 'ti-calendar-stats'],
                                    'custom' => ['label' => 'Custom', 'note' => 'Bitmə tarixini özünüz seçin', 'icon' => 'ti-calendar'],
                                    'lifetime' => ['label' => 'Ömürlük', 'note' => 'Sənədin bitmə tarixi yoxdur', 'icon' => 'ti-infinity'],
                                ];
                            @endphp
                            <div class="cert-validity" data-cert-validity>
                                <div class="cert-validity-options" role="radiogroup" aria-label="{{ $field['label'] }}">
                                    @foreach($validityOptions as $optionValue => $option)
                                        <label class="target-option">
                                            <input type="radio" name="validity_period" value="{{ $optionValue }}" @checked($validityPeriod === $optionValue)>
                                            <span class="target-option-card">
                                                <i class="ti {{ $option['icon'] }}"></i>
                                                <span class="target-option-copy">
                                                    <strong>{{ $option['label'] }}</strong>
                                                    <small>{{ $option['note'] }}</small>
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="cert-validity-custom {{ $validityPeriod === 'custom' ? 'is-active' : '' }}" data-cert-validity-custom>
                                    <div>
                                        <label for="certificate_expire_date">Bitmə tarixi</label>
                                        <input id="certificate_expire_date" type="date" name="expire_date" value="{{ $expireDateValue }}" data-cert-expire-date>
                                    </div>
                                    <div class="cert-validity-summary" data-cert-validity-summary>
                                        <i class="ti ti-clock-check"></i>
                                        <span>Seçilmiş müddətə görə tarix hesablanır.</span>
                                    </div>
                                </div>
                                <div class="cert-validity-summary" data-cert-validity-overview>
                                    <i class="ti ti-shield-check"></i>
                                    <span></span>
                                </div>
                            </div>
                        @elseif($type === 'qr_position')
                            @php
                                $qrX = (float) old('qr_x', $edit?->qr_x ?? 72);
                                $qrY = (float) old('qr_y', $edit?->qr_y ?? 72);
                                $qrSize = (float) old('qr_size', $edit?->qr_size ?? 16);
                                $certificateFile = (string) old('file_path', $edit?->file_path ?? '');
                                $qrPreviewPath = (string) ($edit?->qr_code_path ?? '');
                                $certificateExtension = strtolower(pathinfo($certificateFile, PATHINFO_EXTENSION));
                                $isPdfCertificate = $certificateExtension === 'pdf';
                                $canPreviewCertificate = $certificateFile && in_array($certificateExtension, ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'], true);
                                $hasCertificatePreview = $canPreviewCertificate || $isPdfCertificate;
                                $previewAspect = (float) ($certificateAspectRatio ?? 0);
                            @endphp
                            <div class="cert-qr-placement" data-cert-qr-placement>
                                <input type="hidden" name="qr_x" value="{{ $qrX }}" data-qr-x>
                                <input type="hidden" name="qr_y" value="{{ $qrY }}" data-qr-y>
                                <input type="hidden" name="qr_size" value="{{ $qrSize }}" data-qr-size>
                                <div class="cert-qr-stage {{ $isPdfCertificate ? 'has-media has-pdf' : '' }}" data-qr-stage @if($hasCertificatePreview) data-certificate-src="{{ asset(ltrim($certificateFile, '/')) }}" data-certificate-type="{{ $isPdfCertificate ? 'pdf' : 'image' }}" @endif @if($canPreviewCertificate) style="background-image:url('{{ asset(ltrim($certificateFile, '/')) }}')" @elseif($isPdfCertificate) style="--cert-qr-aspect:{{ $previewAspect > 0 ? $previewAspect : 1.414 }} / 1" @endif>
                                    <embed class="cert-qr-pdf-preview" data-qr-pdf-preview type="application/pdf" @if($isPdfCertificate) src="{{ asset(ltrim($certificateFile, '/')) }}#page=1&toolbar=0&navpanes=0&scrollbar=0&view=Fit" @else hidden @endif>
                                    <span class="cert-qr-empty" data-qr-empty @if($hasCertificatePreview) hidden @endif>
                                        Sertifikat faylı seçildikdən sonra QR kodu bu sahədə istədiyiniz yerə sürüşdürün.
                                    </span>
                                    <div class="cert-qr-box" data-qr-box tabindex="0" role="application" aria-label="QR kodun yerləşimi" style="left:{{ $qrX }}%;top:{{ $qrY }}%;width:{{ $qrSize }}%;">
                                        @if($qrPreviewPath)
                                            <img src="{{ asset(ltrim($qrPreviewPath, '/')) }}" alt="QR">
                                        @else
                                            <span>QR</span>
                                        @endif
                                        <button class="cert-qr-resize" type="button" data-qr-resize aria-label="QR ölçüsünü dəyiş"><i class="ti ti-arrows-diagonal-2"></i></button>
                                    </div>
                                </div>
                                <div class="cert-qr-controls">
                                    <input type="range" min="8" max="35" step="0.1" value="{{ $qrSize }}" data-qr-size-range aria-label="QR ölçüsü">
                                    <strong data-qr-size-label>{{ $qrSize }}%</strong>
                                    <div class="cert-qr-coordinates" aria-label="QR koordinatları">
                                        <span class="cert-qr-coordinate" data-qr-x-label>X: {{ $qrX }}%</span>
                                        <span class="cert-qr-coordinate" data-qr-y-label>Y: {{ $qrY }}%</span>
                                    </div>
                                </div>
                                <div class="cert-qr-help">QR kodu sürüşdürün və ya seçdikdən sonra ox düymələri ilə 0.1% addımla dəqiq yerləşdirin. Shift + ox düyməsi 1% hərəkət etdirir.</div>
                            </div>
                        @elseif($type === 'certificate_revocation')
                            @php
                                $isRevoked = (bool) old('is_revoked', $edit?->status === 'revoked');
                                $revokeReason = (string) old('revoke_reason', $edit?->revoke_reason ?? '');
                            @endphp
                            <div class="cert-revocation" data-cert-revocation>
                                <label class="check menu-switch">
                                    <input type="checkbox" name="is_revoked" value="1" @checked($isRevoked)>
                                    <span class="menu-switch-track" aria-hidden="true"></span>
                                    <span class="menu-switch-copy">
                                        <strong>Sənədi ləğv et</strong>
                                        <small class="menu-switch-label" data-active-label="Ləğv edilib" data-passive-label="Ləğv edilməyib"></small>
                                    </span>
                                </label>
                                <div class="cert-revoke-reason {{ $isRevoked ? 'is-active' : '' }}" data-cert-revoke-reason>
                                    <label for="certificate_revoke_reason">Ləğv səbəbi</label>
                                    <textarea id="certificate_revoke_reason" name="revoke_reason" placeholder="Sənədin niyə ləğv edildiyini qeyd edin...">{{ $revokeReason }}</textarea>
                                </div>
                            </div>
                        @elseif($type === 'select')
                            <select name="{{ $name }}">
                                @foreach($field['options'] as $optionValue => $label)
                                    <option value="{{ $optionValue }}" @selected((string)$value === (string)$optionValue)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($isArticleCategory)
                            <div class="target-choice article-category-choice" role="radiogroup" aria-label="{{ $field['label'] }}">
                                <label class="target-option">
                                    <input type="radio" name="{{ $name }}" value="" @checked(empty($value))>
                                    <span class="target-option-card">
                                        <i class="ti ti-category-off"></i>
                                        <span class="target-option-copy">
                                            <strong>Kateqoriyasız</strong>
                                            <small>Məqalə ümumi siyahıda saxlanılacaq.</small>
                                        </span>
                                    </span>
                                </label>
                                @foreach($categoryOptions as $optionValue => $label)
                                    @php
                                        $categoryRecord = $categoryRecords[$optionValue] ?? [];
                                    @endphp
                                    <label class="target-option">
                                        <input type="radio" name="{{ $name }}" value="{{ $optionValue }}" @checked((int)$value === (int)$optionValue)>
                                        <span class="target-option-card">
                                            @if(!empty($categoryRecord['image_path']))
                                                <img src="{{ asset(ltrim((string)$categoryRecord['image_path'], '/')) }}" alt="">
                                            @elseif(!empty($categoryRecord['icon_class']))
                                                <i class="{{ $categoryRecord['icon_class'] }}"></i>
                                            @else
                                                <i class="ti ti-category"></i>
                                            @endif
                                            <span class="target-option-copy">
                                                <strong>{{ $label }}</strong>
                                                <small>Bu kateqoriya altında görünsün.</small>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($type === 'category')
                            <select name="{{ $name }}">
                                <option value="">Kateqoriyasız</option>
                                @foreach($categoryOptions as $optionValue => $label)
                                    <option value="{{ $optionValue }}" @selected((int)$value === (int)$optionValue)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'menu_parent')
                            <select name="{{ $name }}">
                                <option value="">Ana menyu</option>
                                @foreach($menuParentOptions as $optionValue => $label)
                                    @if(!$edit || (int)$edit->id !== (int)$optionValue)
                                        <option value="{{ $optionValue }}" @selected((int)$value === (int)$optionValue)>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        @elseif($type === 'textarea')
                            <textarea name="{{ $name }}">{{ $value }}</textarea>
                        @elseif(in_array($type, ['file', 'svg_file'], true))
                            @php
                                $fileInputId = 'module_file_' . $moduleKey . '_' . $name;
                            @endphp
                            <div class="module-file-upload">
                                <input id="{{ $fileInputId }}" class="module-file-input" type="file" name="{{ $name }}" accept="{{ $type === 'svg_file' ? '.svg,image/svg+xml' : ($moduleKey === 'certificates_manage' ? 'image/*,.pdf,application/pdf' : 'image/*') }}" data-module-file-input>
                                <label class="module-file-drop" for="{{ $fileInputId }}">
                                    <span class="module-file-icon"><i class="ti {{ $type === 'svg_file' ? 'ti-file-type-svg' : 'ti-photo-up' }}"></i></span>
                                    <span class="module-file-copy">
                                        <strong>{{ $type === 'svg_file' ? 'SVG seç' : 'Şəkil seç' }}</strong>
                                        <small data-module-file-name>{{ $type === 'svg_file' ? 'Yalnız SVG faylı qəbul olunur' : ($moduleKey === 'certificates_manage' ? 'PNG, JPG, WEBP, SVG və ya PDF faylı yüklə' : 'PNG, JPG və ya WEBP faylı yüklə') }}</small>
                                    </span>
                                </label>
                                @if($value)
                                    <div class="module-file-current">
                                        @if($moduleKey === 'certificates_manage' && strtolower(pathinfo((string) $value, PATHINFO_EXTENSION)) === 'pdf')
                                            <span class="preview-img" style="display:grid;place-items:center"><i class="ti ti-file-type-pdf"></i></span>
                                        @else
                                            <img class="preview-img" src="{{ asset(ltrim((string)$value, '/')) }}" alt="">
                                        @endif
                                        <span>{{ basename((string)$value) }}</span>
                                    </div>
                                @endif
                            </div>
                        @elseif($type === 'slug')
                            <input type="text" name="{{ $name }}" value="{{ $value }}" data-slug-input data-slug-dirty="{{ ($edit || old($name)) ? '1' : '0' }}">
                        @elseif($type === 'datetime')
                            <input type="datetime-local" name="{{ $name }}" value="{{ $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}">
                        @else
                            <input type="{{ $type === 'number' ? 'number' : ($type === 'date' ? 'date' : 'text') }}" name="{{ $name }}" value="{{ $value }}" @required($field['required'] ?? false) @if($name === 'title') data-slug-source @endif @if($moduleKey === 'certificates_manage' && $name === 'issue_date') data-cert-issue-date @endif>
                        @endif
                    @endif
                </div>
            @endforeach

            @if($moduleKey === 'articles')
                <div class="form-row full">
                    <label class="article-notify-option">
                        <input type="checkbox" name="send_notify" value="1">
                        <span class="article-notify-icon"><i class="ti ti-mail-forward"></i></span>
                        <span class="article-notify-copy">
                            <strong>Qeydiyyatlı istifadəçilərə email bildirişi göndər</strong>
                            <small>Eyni məqalə eyni email-ə təkrar göndərilmir.</small>
                        </span>
                        <span class="article-notify-check" aria-hidden="true"></span>
                    </label>
                </div>
            @endif
        </div>
        <div class="{{ $isModalModule ? 'module-modal-actions' : '' }}">
            <button class="btn {{ $isModalModule ? 'module-save-btn' : '' }}" type="submit" @unless($isModalModule) style="margin-top:18px" @endunless>Yadda saxla</button>
            @if($edit)<a class="btn btn-light" @unless($isModalModule) style="margin-top:18px" @endunless href="{{ route('admin.modules.index', ['module' => $moduleKey]) }}">Ləğv et</a>@endif
        </div>
    </form>
    @if($isModalModule)
        </div>
    @endif
</div>

<div class="card module-results" style="margin-top:22px" data-module-results>
    @php
        $visibleColumns = collect($module['columns'])->reject(fn ($column) => $isSortableModule && $column === 'sort_order')->values();
        $columnLabels = $visibleColumns->mapWithKeys(fn ($column) => [$column => $moduleKey === 'categories' && $column === 'visual' ? 'Vizual' : ($moduleKey === 'certificates_manage' && $column === 'is_active' ? 'Aktivlik' : ($isModalModule && $column === 'is_active' ? 'Status' : ($module['fields'][$column]['label'] ?? Str::headline($column))))]);
    @endphp
    <table class="module-table">
        <thead><tr>@if($isSortableModule)<th class="drag-cell"></th>@endif @foreach($visibleColumns as $column)<th>{{ $columnLabels[$column] }}</th>@endforeach<th>Əməliyyat</th></tr></thead>
        <tbody @if($isSortableModule) id="moduleSortable" @endif>
        @forelse($rows as $row)
            <tr @if($isSortableModule) draggable="true" data-id="{{ $row->id }}" @endif>
                @if($isSortableModule)
                    <td class="drag-cell"><button class="drag-handle" type="button" aria-label="Sırala"><i class="ti ti-grip-vertical"></i></button></td>
                @endif
                @foreach($visibleColumns as $column)
                    <td>
                        @php
                            $cell = $column === 'visual' ? null : $row->{$column};
                        @endphp
                        @if($moduleKey === 'categories' && $column === 'visual')
                            <span class="category-visual-cell">
                                @if($row->image_path)
                                    <img class="preview-img" src="{{ asset(ltrim((string)$row->image_path, '/')) }}" alt="">
                                    <span>SVG şəkil</span>
                                @elseif($row->icon_class)
                                    <i class="{{ $row->icon_class }}"></i>
                                    <span>{{ $row->icon_class }}</span>
                                @else
                                    -
                                @endif
                            </span>
                        @elseif($column === 'parent_id')
                            @if($cell)<span class="parent-badge">#{{ $cell }}</span>@else <span class="parent-badge">Ana</span> @endif
                        @elseif(str_contains($column, 'image') || str_contains($column, 'logo') || str_contains($column, 'file') || str_contains($column, 'cover'))
                            @if($cell)<img class="preview-img" src="{{ asset(ltrim((string)$cell, '/')) }}" alt="">@else - @endif
                        @elseif($moduleKey === 'certificates_manage' && $column === 'status')
                            @php
                                $effectiveStatus = $row->effectiveStatus();
                                $certificateStatusLabels = ['valid' => 'Keçərlidir', 'expired' => 'Müddəti bitib', 'revoked' => 'Ləğv edilib'];
                            @endphp
                            <span class="parent-badge">{{ $certificateStatusLabels[$effectiveStatus] ?? 'Naməlum' }}</span>
                        @elseif(isset($module['fields'][$column]['options']) && array_key_exists((string) $cell, $module['fields'][$column]['options']))
                            {{ $module['fields'][$column]['options'][(string) $cell] }}
                        @elseif(is_bool($cell) || in_array($column, ['is_active','is_featured'], true))
                            <span class="status-icon {{ $cell ? 'is-on' : 'is-off' }}" title="{{ $cell ? 'Aktiv' : 'Passiv' }}" aria-label="{{ $cell ? 'Aktiv' : 'Passiv' }}"><i class="ti {{ $cell ? 'ti-check' : 'ti-x' }}"></i></span>
                        @else
                            {{ Str::limit((string)$cell, 80) }}
                        @endif
                    </td>
                @endforeach
                <td>
                    <div class="actions">
                        <a class="btn btn-light action-icon" href="{{ route('admin.modules.index', ['module' => $moduleKey, 'edit' => $row->id] + ($isModalModule ? [] : request()->only(['q','status','active']))) }}" title="Redaktə et" aria-label="Redaktə et"><i class="ti ti-pencil"></i></a>
                        @if($moduleKey === 'articles')
                            <form method="post" action="{{ route('admin.articles.notify', $row) }}" onsubmit="return confirm('Bu məqalə üçün email bildirişi göndərilsin?')">
                                @csrf
                                <button class="btn btn-green action-icon" type="submit" title="Email göndər" aria-label="Email göndər"><i class="ti ti-mail-forward"></i></button>
                            </form>
                        @endif
                        <form method="post" action="{{ route('admin.modules.destroy', ['module' => $moduleKey, 'id' => $row->id]) }}" onsubmit="return confirm('Silinsin?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger action-icon" type="submit" title="Sil" aria-label="Sil"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ $visibleColumns->count() + 1 + ($isSortableModule ? 1 : 0) }}">Məlumat yoxdur.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $rows->links() }}</div>
</div>
<script>
(() => {
    document.querySelectorAll('[data-module-toast]').forEach((toast) => {
        const dismiss = () => {
            toast.classList.add('is-hiding');
            window.setTimeout(() => toast.remove(), 220);
        };

        window.setTimeout(dismiss, 4200);
    });

    const filterForm = document.querySelector('[data-module-filter-form]');
    if (filterForm) {
        const isAjaxFilter = @json($isAjaxFilterModule);
        let resultsCard = document.querySelector('[data-module-results]');
        let filterTimer = null;
        let filterAbort = null;

        const searchInput = filterForm.querySelector('[data-filter-debounce]');
        const canRunSearch = () => {
            if (!searchInput) return true;
            const minChars = Number(searchInput.dataset.filterMinChars || 0);
            const value = searchInput.value.trim();
            return value.length === 0 || value.length >= minChars;
        };

        const buildFilterUrl = () => {
            const url = new URL(filterForm.action, window.location.href);
            new FormData(filterForm).forEach((value, key) => {
                if (String(value).trim() === '') {
                    url.searchParams.delete(key);
                    return;
                }
                url.searchParams.set(key, value);
            });
            url.searchParams.delete('page');
            return url;
        };

        const fetchFilter = async (url) => {
            if (!canRunSearch()) return;
            filterAbort?.abort();
            filterAbort = new AbortController();
            resultsCard?.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    signal: filterAbort.signal,
                });
                if (!response.ok) throw new Error('Filter request failed');

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextResults = doc.querySelector('[data-module-results]');
                if (nextResults && resultsCard) {
                    resultsCard.replaceWith(nextResults);
                    resultsCard = nextResults;
                }
                window.history.replaceState({}, '', url);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error(error);
                }
            } finally {
                resultsCard?.classList.remove('is-loading');
            }
        };

        const submitFilter = () => {
            window.clearTimeout(filterTimer);
            if (!isAjaxFilter) {
                filterForm.requestSubmit ? filterForm.requestSubmit() : filterForm.submit();
                return;
            }
            fetchFilter(buildFilterUrl());
        };

        filterForm.querySelectorAll('[data-filter-debounce]').forEach((input) => {
            input.addEventListener('input', () => {
                window.clearTimeout(filterTimer);
                filterTimer = window.setTimeout(submitFilter, 450);
            });
        });

        filterForm.querySelectorAll('[data-filter-auto-submit]').forEach((input) => {
            input.addEventListener('change', submitFilter);
        });

        filterForm.addEventListener('submit', (event) => {
            if (!isAjaxFilter) return;
            event.preventDefault();
            submitFilter();
        });

        document.addEventListener('click', (event) => {
            if (!isAjaxFilter) return;
            const link = event.target.closest('[data-module-results] .pagination a');
            if (!link) return;
            event.preventDefault();
            fetchFilter(new URL(link.href, window.location.href));
        });
    }
})();
</script>
@if($isModalModule)
<script>
(() => {
    const modal = document.querySelector('[data-module-modal]');
    const openButton = document.querySelector('[data-module-modal-open]');
    const closeButton = document.querySelector('[data-module-modal-close]');
    const closeUrl = @json(route('admin.modules.index', ['module' => $moduleKey]));
    const hasEditForm = @json((bool) $edit);

    if (modal?.classList.contains('is-open')) {
        document.body.classList.add('module-modal-open');
    }

    openButton?.addEventListener('click', () => {
        if (hasEditForm) {
            window.location.href = `${closeUrl}?create=1`;
            return;
        }

        modal?.classList.add('is-open');
        document.body.classList.add('module-modal-open');
    });
    closeButton?.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal();
    });
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
            closeModal();
        }
    });

    function closeModal() {
        modal?.classList.remove('is-open');
        document.body.classList.remove('module-modal-open');
        if (window.location.href !== closeUrl) {
            window.history.replaceState({}, '', closeUrl);
        }
    }

    function updateSwitchLabels() {
        document.querySelectorAll('.menu-switch').forEach((switchEl) => {
            const input = switchEl.querySelector('input[type="checkbox"]');
            const label = switchEl.querySelector('.menu-switch-label');
            if (!input || !label) return;
            label.textContent = input.checked ? label.dataset.activeLabel : label.dataset.passiveLabel;
        });
    }

    document.querySelectorAll('.menu-switch input[type="checkbox"]').forEach((input) => {
        input.addEventListener('change', updateSwitchLabels);
    });
    updateSwitchLabels();

    document.querySelectorAll('[data-module-file-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const wrapper = input.closest('.module-file-upload');
            const name = wrapper?.querySelector('[data-module-file-name]');
            if (!name) return;
            if (!name.dataset.defaultLabel) {
                name.dataset.defaultLabel = name.textContent;
            }
            const file = input.files?.[0];
            name.textContent = file?.name || name.dataset.defaultLabel;
            updateCertificateQrPreview(file);
        });
    });

    function updateCertificateQrPreview(file) {
        const qrPlacement = document.querySelector('[data-cert-qr-placement]');
        if (!qrPlacement) return;

        const stage = qrPlacement.querySelector('[data-qr-stage]');
        const empty = qrPlacement.querySelector('[data-qr-empty]');
        if (!stage || !file) return;

        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        if (isPdf) {
            setCertificateStagePdf(stage, URL.createObjectURL(file), file);
            empty.hidden = true;
            return;
        }

        const isPreviewable = file.type.startsWith('image/') || file.name.toLowerCase().endsWith('.svg');
        if (!isPreviewable) {
            stage.style.backgroundImage = '';
            stage.classList.remove('has-media');
            stage.classList.remove('has-pdf');
            stage.style.removeProperty('--cert-qr-aspect');
            const pdfPreview = stage.querySelector('[data-qr-pdf-preview]');
            if (pdfPreview) pdfPreview.hidden = true;
            empty.hidden = false;
            empty.textContent = 'Bu fayl üçün preview göstərilə bilmir.';
            return;
        }

        setCertificateStageImage(stage, URL.createObjectURL(file));
        empty.hidden = true;
    }

    function setCertificateStageImage(stage, src) {
        const pdfPreview = stage.querySelector('[data-qr-pdf-preview]');
        if (pdfPreview) pdfPreview.hidden = true;
        stage.classList.remove('has-pdf');
        const image = new Image();
        image.onload = () => {
            if (image.naturalWidth > 0 && image.naturalHeight > 0) {
                stage.style.setProperty('--cert-qr-aspect', `${image.naturalWidth} / ${image.naturalHeight}`);
                stage.classList.add('has-media');
                stage.dispatchEvent(new Event('certificate-stage-ready'));
            }
        };
        image.src = src;
        stage.style.backgroundImage = `url("${src}")`;
    }

    async function setCertificateStagePdf(stage, src, file = null) {
        const pdfPreview = stage.querySelector('[data-qr-pdf-preview]');
        if (!pdfPreview) return;

        stage.style.backgroundImage = '';
        stage.classList.add('has-media', 'has-pdf');
        pdfPreview.hidden = false;
        pdfPreview.src = `${src}#page=1&toolbar=0&navpanes=0&scrollbar=0&view=Fit`;

        if (file) {
            try {
                const bytes = new Uint8Array(await file.arrayBuffer());
                const header = new TextDecoder('latin1').decode(bytes);
                const match = header.match(/\/(?:CropBox|MediaBox)\s*\[\s*(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s*\]/);
                if (match) {
                    const width = Math.abs(Number(match[3]) - Number(match[1]));
                    const height = Math.abs(Number(match[4]) - Number(match[2]));
                    if (width > 0 && height > 0) {
                        stage.style.setProperty('--cert-qr-aspect', `${width} / ${height}`);
                    }
                }
            } catch (error) {
                console.warn('PDF preview ölçüsü oxunmadı.', error);
            }
        }

        window.requestAnimationFrame(() => stage.dispatchEvent(new Event('certificate-stage-ready')));
    }

    document.querySelectorAll('[data-cert-qr-placement]').forEach((placement) => {
        const stage = placement.querySelector('[data-qr-stage]');
        const box = placement.querySelector('[data-qr-box]');
        const inputX = placement.querySelector('[data-qr-x]');
        const inputY = placement.querySelector('[data-qr-y]');
        const inputSize = placement.querySelector('[data-qr-size]');
        const sizeRange = placement.querySelector('[data-qr-size-range]');
        const sizeLabel = placement.querySelector('[data-qr-size-label]');
        const xLabel = placement.querySelector('[data-qr-x-label]');
        const yLabel = placement.querySelector('[data-qr-y-label]');
        if (!stage || !box || !inputX || !inputY || !inputSize) return;

        if (stage.dataset.certificateSrc) {
            if (stage.dataset.certificateType === 'pdf') {
                setCertificateStagePdf(stage, stage.dataset.certificateSrc);
            } else {
                setCertificateStageImage(stage, stage.dataset.certificateSrc);
            }
        }

        const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
        const precise = (value) => Number(value).toFixed(3).replace(/\.?0+$/, '');
        const render = () => {
            const size = clamp(Number(inputSize.value || 16), 8, 35);
            const width = stage.clientWidth || 1;
            const height = stage.clientHeight || width;
            const sizeY = (width * (size / 100) / height) * 100;
            const x = clamp(Number(inputX.value || 72), 0, Math.max(0, 100 - size));
            const y = clamp(Number(inputY.value || 72), 0, Math.max(0, 100 - sizeY));
            inputX.value = precise(x);
            inputY.value = precise(y);
            inputSize.value = precise(size);
            box.style.left = `${inputX.value}%`;
            box.style.top = `${inputY.value}%`;
            box.style.width = `${inputSize.value}%`;
            if (sizeRange) sizeRange.value = inputSize.value;
            if (sizeLabel) sizeLabel.textContent = `Ölçü: ${Number(inputSize.value).toFixed(1)}%`;
            if (xLabel) xLabel.textContent = `X: ${Number(inputX.value).toFixed(1)}%`;
            if (yLabel) yLabel.textContent = `Y: ${Number(inputY.value).toFixed(1)}%`;
        };

        let dragMode = null;
        let start = null;
        const begin = (event, mode) => {
            event.preventDefault();
            dragMode = mode;
            const point = event.touches?.[0] || event;
            start = {
                x: point.clientX,
                y: point.clientY,
                left: Number(inputX.value || 72),
                top: Number(inputY.value || 72),
                size: Number(inputSize.value || 16),
            };
            window.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);
            window.addEventListener('touchmove', move, {passive: false});
            window.addEventListener('touchend', end);
        };
        const move = (event) => {
            if (!dragMode || !start) return;
            event.preventDefault();
            const point = event.touches?.[0] || event;
            const rect = stage.getBoundingClientRect();
            const dx = ((point.clientX - start.x) / rect.width) * 100;
            const dy = ((point.clientY - start.y) / rect.height) * 100;

            if (dragMode === 'resize') {
                const pixelDelta = Math.max(point.clientX - start.x, point.clientY - start.y);
                inputSize.value = precise(clamp(start.size + (pixelDelta / rect.width) * 100, 8, 35));
            } else {
                const size = Number(inputSize.value || 16);
                const sizeY = (rect.width * (size / 100) / rect.height) * 100;
                inputX.value = precise(clamp(start.left + dx, 0, 100 - size));
                inputY.value = precise(clamp(start.top + dy, 0, 100 - sizeY));
            }
            render();
        };
        const end = () => {
            dragMode = null;
            start = null;
            window.removeEventListener('mousemove', move);
            window.removeEventListener('mouseup', end);
            window.removeEventListener('touchmove', move);
            window.removeEventListener('touchend', end);
        };

        box.addEventListener('mousedown', (event) => {
            if (event.target.closest('[data-qr-resize]')) return;
            begin(event, 'move');
        });
        box.addEventListener('touchstart', (event) => {
            if (event.target.closest('[data-qr-resize]')) return;
            begin(event, 'move');
        }, {passive: false});
        box.querySelector('[data-qr-resize]')?.addEventListener('mousedown', (event) => begin(event, 'resize'));
        box.querySelector('[data-qr-resize]')?.addEventListener('touchstart', (event) => begin(event, 'resize'), {passive: false});
        sizeRange?.addEventListener('input', () => {
            inputSize.value = sizeRange.value;
            render();
        });
        box.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(event.key)) return;
            event.preventDefault();
            const step = event.shiftKey ? 1 : 0.1;
            if (event.key === 'ArrowLeft') inputX.value = Number(inputX.value) - step;
            if (event.key === 'ArrowRight') inputX.value = Number(inputX.value) + step;
            if (event.key === 'ArrowUp') inputY.value = Number(inputY.value) - step;
            if (event.key === 'ArrowDown') inputY.value = Number(inputY.value) + step;
            render();
        });
        stage.addEventListener('certificate-stage-ready', render);
        window.addEventListener('resize', () => window.requestAnimationFrame(render));
        render();
    });

    document.querySelectorAll('[data-cert-validity]').forEach((validity) => {
        const issueInput = document.querySelector('[data-cert-issue-date]');
        const customPanel = validity.querySelector('[data-cert-validity-custom]');
        const expireInput = validity.querySelector('[data-cert-expire-date]');
        const customSummary = validity.querySelector('[data-cert-validity-summary] span');
        const overview = validity.querySelector('[data-cert-validity-overview] span');

        const addYearsNoOverflow = (value, years) => {
            const parts = String(value || '').split('-').map(Number);
            if (parts.length !== 3 || parts.some((part) => !Number.isFinite(part))) return '';
            const [year, month, day] = parts;
            const targetYear = year + years;
            const lastDay = new Date(Date.UTC(targetYear, month, 0)).getUTCDate();
            return [
                targetYear,
                String(month).padStart(2, '0'),
                String(Math.min(day, lastDay)).padStart(2, '0'),
            ].join('-');
        };

        const formatDate = (value) => {
            const [year, month, day] = String(value || '').split('-');
            return year && month && day ? `${day}.${month}.${year}` : '';
        };

        const updateValidity = () => {
            const selected = validity.querySelector('input[name="validity_period"]:checked')?.value || '2_years';
            const isCustom = selected === 'custom';
            customPanel?.classList.toggle('is-active', isCustom);
            if (expireInput) expireInput.required = isCustom;

            if (selected === 'lifetime') {
                if (overview) overview.textContent = 'Sənəd ömürlük etibarlı olacaq.';
                return;
            }

            if (selected === 'custom') {
                const formatted = formatDate(expireInput?.value);
                if (customSummary) {
                    customSummary.textContent = formatted
                        ? `Custom bitmə tarixi: ${formatted}`
                        : 'Bitmə tarixini seçin.';
                }
                if (overview) {
                    overview.textContent = formatted
                        ? `Sənəd ${formatted} tarixinədək etibarlı olacaq.`
                        : 'Custom bitmə tarixi gözlənilir.';
                }
                return;
            }

            const years = selected === '1_year' ? 1 : (selected === '3_years' ? 3 : 2);
            const calculated = addYearsNoOverflow(issueInput?.value, years);
            if (overview) {
                overview.textContent = calculated
                    ? `Sənəd ${formatDate(calculated)} tarixinədək etibarlı olacaq.`
                    : `Verilmə tarixi seçildikdən sonra ${years} illik müddət hesablanacaq.`;
            }
        };

        validity.querySelectorAll('input[name="validity_period"]').forEach((input) => {
            input.addEventListener('change', updateValidity);
        });
        issueInput?.addEventListener('change', updateValidity);
        expireInput?.addEventListener('change', updateValidity);
        updateValidity();
    });

    document.querySelectorAll('[data-cert-revocation]').forEach((revocation) => {
        const toggle = revocation.querySelector('input[name="is_revoked"]');
        const reasonPanel = revocation.querySelector('[data-cert-revoke-reason]');
        const reasonInput = revocation.querySelector('textarea[name="revoke_reason"]');
        const updateRevocation = () => {
            const isRevoked = Boolean(toggle?.checked);
            reasonPanel?.classList.toggle('is-active', isRevoked);
            if (reasonInput) reasonInput.required = isRevoked;
        };
        toggle?.addEventListener('change', updateRevocation);
        updateRevocation();
    });

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/ə/g, 'e')
            .replace(/ö/g, 'o')
            .replace(/ü/g, 'u')
            .replace(/ı/g, 'i')
            .replace(/i̇/g, 'i')
            .replace(/ğ/g, 'g')
            .replace(/ş/g, 's')
            .replace(/ç/g, 'c')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .replace(/-{2,}/g, '-');
    }

    const slugSource = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-input]');
    if (slugSource && slugInput) {
        slugInput.addEventListener('input', () => {
            slugInput.dataset.slugDirty = '1';
        });
        slugSource.addEventListener('input', () => {
            if (slugInput.dataset.slugDirty === '1') return;
            slugInput.value = slugify(slugSource.value);
        });
    }

    function updateCategoryVisualPickers() {
        document.querySelectorAll('[data-category-visual]').forEach((picker) => {
            const type = picker.querySelector('input[name="category_visual_type"]:checked')?.value || 'icon';
            const iconPanel = picker.querySelector('[data-category-icon-panel]');
            const imagePanel = picker.querySelector('[data-category-image-panel]');
            iconPanel?.classList.toggle('is-active', type === 'icon');
            imagePanel?.classList.toggle('is-active', type === 'image');
            picker.querySelectorAll('[name="icon_class"]').forEach((input) => {
                input.disabled = type !== 'icon';
            });
            picker.querySelectorAll('[name="image_path"]').forEach((input) => {
                input.disabled = type !== 'image';
            });
        });
    }

    document.querySelectorAll('[data-category-visual] input[name="category_visual_type"]').forEach((input) => {
        input.addEventListener('change', updateCategoryVisualPickers);
    });
    updateCategoryVisualPickers();

    const list = document.getElementById('moduleSortable');
    if (!list) return;

    let dragged = null;
    list.querySelectorAll('tr[data-id]').forEach((row) => {
        row.addEventListener('dragstart', (event) => {
            dragged = row;
            row.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
        });

        row.addEventListener('dragend', () => {
            row.classList.remove('dragging');
            list.querySelectorAll('.drag-over').forEach((item) => item.classList.remove('drag-over'));
            saveOrder();
        });
    });

    list.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (!dragged) return;
        const after = getDragAfterElement(list, event.clientY);
        list.querySelectorAll('.drag-over').forEach((item) => item.classList.remove('drag-over'));
        if (after == null) {
            list.appendChild(dragged);
        } else {
            after.classList.add('drag-over');
            list.insertBefore(dragged, after);
        }
    });

    function getDragAfterElement(container, y) {
        const rows = [...container.querySelectorAll('tr[data-id]:not(.dragging)')];
        return rows.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) return {offset, element: child};
            return closest;
        }, {offset: Number.NEGATIVE_INFINITY, element: null}).element;
    }

    async function saveOrder() {
        const order = [...list.querySelectorAll('tr[data-id]')].map((row) => row.dataset.id);
        const form = new FormData();
        form.append('_token', @json(csrf_token()));
        form.append('order', JSON.stringify(order));
        await fetch(@json(route('admin.modules.sort', ['module' => $moduleKey])), {method: 'POST', body: form});
    }
})();
</script>
@endif
@endsection
