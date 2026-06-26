<!doctype html>
<html lang="{{ $selectedLanguage ?? 'az' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Canlı vizual redaktor</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
    :root{
        --admin-bg:#e7f5f7;
        --admin-shell:#deeeeb;
        --admin-card:#fff;
        --admin-text:#151719;
        --admin-muted:#63747a;
        --admin-soft:#eef8f6;
        --admin-line:#d8e9e7;
        --admin-line-2:#e8f1ef;
        --admin-accent:#ffd166;
        --admin-accent-soft:#fff3cc;
        --admin-danger:#c94c5a;
        --admin-success:#2f9b6d;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;background:radial-gradient(circle at 9% 6%,rgba(184,229,231,.72),transparent 31%),radial-gradient(circle at 92% 2%,rgba(226,233,255,.72),transparent 29%),linear-gradient(135deg,#edf9f8 0%,var(--admin-bg) 48%,#e8f1fb 100%);color:var(--admin-text);font-family:"Noto Sans","Segoe UI",Arial,sans-serif;font-weight:650;overflow:hidden}
    a{text-decoration:none;color:inherit}
    button,input,textarea,select{font:inherit}
    label{display:block;margin:0 0 6px;font-size:12px;font-weight:900;color:#1f2327}
    input,textarea,select{width:100%;border:1px solid var(--admin-line);border-radius:11px;padding:10px 12px;font-size:13px;font-weight:750;outline:0;background:#fff;color:#111;transition:border-color .16s ease,box-shadow .16s ease,background-color .16s ease}
    textarea{min-height:116px;resize:vertical}
    input:focus,textarea:focus,select:focus{border-color:#9fd7d0;box-shadow:0 0 0 4px rgba(117,214,200,.16)}
    input[type="checkbox"]{width:auto;accent-color:#ff7a1a}
    .btn{border:1px solid transparent;border-radius:11px;padding:9px 13px;font-weight:900;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;font-size:13px;text-decoration:none;background:#111;color:#fff;line-height:1.2;transition:transform .16s ease,box-shadow .16s ease,background-color .16s ease,border-color .16s ease}
    .btn i{font-size:16px;line-height:1;color:currentColor}
    .btn:hover{transform:translateY(-1px)}
    .btn-primary{background:#ff7a1a;color:#fff;box-shadow:0 12px 28px rgba(255,122,26,.22)}
    .btn-light{background:#fff;color:#111;border-color:var(--admin-line);box-shadow:0 6px 18px rgba(25,26,27,.035)}
    .alert-error{background:#fff1f1;border:1px solid #fecaca;color:#b42318;border-radius:13px;padding:12px 14px;margin-bottom:16px;font-weight:850;font-size:13px}
    .ve-shell{min-height:100vh;display:grid;grid-template-rows:auto minmax(0,1fr);gap:14px;padding:12px}
    .ve-statusbar{display:flex;align-items:center;justify-content:space-between;gap:14px;background:#fff;border:1px solid var(--admin-line-2);border-radius:16px;padding:13px 14px;box-shadow:0 12px 32px rgba(61,125,131,.045)}
    .ve-status{display:inline-flex;align-items:center;gap:9px;color:#263237;font-size:13px;font-weight:850;line-height:1.35}
    .ve-status i{width:28px;height:28px;border-radius:999px;display:grid;place-items:center;background:#eaf9ef;color:#1f8f4d;font-size:17px}
    .ve-status.dirty i{background:#fff3cc;color:#9a6500}
    .ve-status.error i{background:#fff1f2;color:#c94c5a}
    .ve-status-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .ve-page-picker{height:39px;display:inline-flex;align-items:center;gap:8px;margin:0;border:1px solid var(--admin-line);border-radius:12px;background:#f8fbfa;padding:0 10px;color:#263237}
    .ve-page-picker i{font-size:17px;color:#718084}
    .ve-page-picker select{height:37px;width:auto;min-width:170px;border:0;background:transparent;padding:0 24px 0 0;box-shadow:none;font-size:12px;font-weight:900;color:#111;cursor:pointer}
    .ve-page-picker select:focus{box-shadow:none;border:0}
    .ve-workspace{display:grid;grid-template-columns:292px minmax(0,1fr) 334px;gap:14px;height:calc(100vh - 92px);min-height:0;transition:grid-template-columns .18s ease}
    .ve-workspace.tree-collapsed{grid-template-columns:64px minmax(0,1fr) 334px}
    .ve-workspace.inspector-collapsed{grid-template-columns:292px minmax(0,1fr) 64px}
    .ve-workspace.tree-collapsed.inspector-collapsed{grid-template-columns:64px minmax(0,1fr) 64px}
    .ve-panel{min-height:0;background:#fff;border:1px solid var(--admin-line-2);border-radius:18px;box-shadow:0 16px 40px rgba(61,125,131,.055);overflow:hidden;display:flex;flex-direction:column}
    .ve-panel-head{min-height:58px;padding:14px 16px;border-bottom:1px solid var(--admin-line-2);display:flex;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(180deg,#fff 0%,#f8fbfa 100%)}
    .ve-panel-head h2{margin:0;color:#111;font-size:15px;font-weight:900;letter-spacing:-.015em}
    .ve-panel-head span{display:block;color:var(--admin-muted);font-size:11px;font-weight:800;margin-top:2px}
    .ve-panel-body{padding:14px;overflow:auto;min-height:0;flex:1}
    .ve-panel-body::-webkit-scrollbar,.ve-frame-wrap::-webkit-scrollbar{width:7px;height:7px}
    .ve-panel-body::-webkit-scrollbar-thumb,.ve-frame-wrap::-webkit-scrollbar-thumb{background:#c5dbd7;border-radius:999px}
    .ve-object-panel{transition:width .18s ease}
    .ve-object-panel .ve-panel-head{min-height:64px}
    .tree-collapsed .ve-object-panel .ve-panel-head{justify-content:center;padding:12px}
    .tree-collapsed .ve-object-panel .ve-panel-head>div,.tree-collapsed .ve-object-panel .ve-panel-body{display:none}
    .ve-tree-toggle.is-collapsed i{transform:rotate(180deg)}
    .ve-inspector{transition:width .18s ease}
    .ve-inspector .ve-panel-head{min-height:64px}
    .inspector-collapsed .ve-inspector .ve-panel-head{justify-content:center;padding:12px}
    .inspector-collapsed .ve-inspector .ve-panel-head>div,.inspector-collapsed .ve-inspector .ve-panel-body{display:none}
    .ve-inspector-toggle.is-collapsed i{transform:rotate(180deg)}
    .ve-pages{display:grid;gap:7px}
    .ve-page-link{display:flex;align-items:center;gap:9px;padding:9px 10px;border:1px solid var(--admin-line-2);border-radius:12px;background:#f8fbfa;color:#253135;font-size:12px;font-weight:850;line-height:1.25}
    .ve-page-link i{color:#718084;font-size:17px}
    .ve-page-link:hover,.ve-page-link.active{background:#fff6ed;border-color:#ffc790;color:#111}
    .ve-page-link.active i{color:#ff7a1a}
    .ve-divider{height:1px;background:var(--admin-line-2);margin:14px 0}
    .ve-mini-title{margin:0 0 8px;color:#6c7e83;font-size:11px;font-weight:900;text-transform:none}
    .ve-tree{display:block}
    .ve-tree-empty{border:1px dashed #cfe1df;border-radius:13px;padding:14px;color:var(--admin-muted);font-size:12px;font-weight:800;line-height:1.5;background:#f8fbfa;text-align:center}
    .ve-tree-node{display:block;margin-bottom:3px}
    .ve-tree-children{display:none;margin:3px 0 0 13px;padding-left:7px;border-left:1px solid var(--admin-line-2)}
    .ve-tree-node.is-open>.ve-tree-children{display:block}
    .ve-tree-row{width:100%;border:1px solid transparent;border-radius:11px;background:transparent;color:#293439;display:grid;grid-template-columns:22px 20px minmax(0,1fr);gap:6px;align-items:center;text-align:left;padding:5px 8px;font-size:12px;font-weight:830;transition:background-color .15s ease,border-color .15s ease,color .15s ease}
    .ve-tree-disclosure{width:22px;height:28px;border:0;border-radius:8px;background:transparent;color:#8aa0a5;display:grid;place-items:center;cursor:pointer;padding:0}
    .ve-tree-disclosure i{font-size:14px;transition:transform .15s ease}
    .ve-tree-node.is-open>.ve-tree-row .ve-tree-disclosure i{transform:rotate(90deg)}
    .ve-tree-disclosure.is-empty{opacity:.22;cursor:default;pointer-events:none}
    .ve-tree-select{min-width:0;border:0;background:transparent;color:inherit;text-align:left;padding:0;cursor:pointer;font:inherit;font-weight:inherit}
    .ve-tree-row>i{color:#718084;font-size:16px}
    .ve-tree-select small{display:block;margin-top:2px;color:#819397;font-size:10px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .ve-tree-row:hover{background:#f4faf8;border-color:var(--admin-line-2)}
    .ve-tree-row.active{background:#fff6ed;border-color:#ffc790;color:#111}
    .ve-tree-row.active>i{color:#ff7a1a}
    .ve-canvas{min-width:0}
    .ve-canvas-toolbar{min-height:58px;padding:10px 12px;border-bottom:1px solid var(--admin-line-2);display:flex;align-items:center;justify-content:space-between;gap:12px;background:#fff}
    .ve-toolbar-group{display:flex;align-items:center;gap:7px;flex-wrap:wrap}
    .ve-tool-btn{width:38px;height:38px;border-radius:12px;border:1px solid var(--admin-line);background:#fff;color:#263237;display:grid;place-items:center;cursor:pointer;font-size:18px;line-height:1;transition:background-color .16s ease,border-color .16s ease,transform .16s ease}
    .ve-tool-btn:hover{background:#fff6ed;border-color:#ffc790;transform:translateY(-1px)}
    .ve-tool-btn:disabled{opacity:.42;cursor:not-allowed;transform:none}
    .ve-tool-btn.is-danger:hover{background:#fff1f2;border-color:#ffd0d5;color:#c94c5a}
    .ve-frame-wrap{padding:12px;background:linear-gradient(135deg,#f6fbfa,#edf8f6);min-height:0;flex:1;overflow:auto;display:flex;justify-content:center}
    .ve-frame-card{height:100%;min-height:560px;width:100%;background:#fff;border:1px solid var(--admin-line);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 58px rgba(61,125,131,.08);transition:width .18s ease,box-shadow .18s ease}
    .ve-frame-card.viewport-mobile{width:390px;max-width:100%;box-shadow:0 22px 66px rgba(61,125,131,.14)}
    .ve-frame-head{height:38px;padding:0 13px;border-bottom:1px solid var(--admin-line-2);display:flex;align-items:center;justify-content:space-between;gap:12px;background:#f8fbfa;color:#607278;font-size:11px;font-weight:850}
    .ve-frame-head strong{color:#111;font-size:12px;font-weight:900}
    .ve-frame{width:100%;flex:1;border:0;background:#fff;min-height:0}
    .ve-viewport-toggle{height:38px;display:inline-grid;grid-template-columns:repeat(2,minmax(0,1fr));border:1px solid var(--admin-line);border-radius:12px;background:#f8fbfa;padding:3px;gap:3px}
    .ve-viewport-toggle button{border:0;background:transparent;color:#63747a;border-radius:9px;padding:0 9px;font-size:12px;font-weight:900;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
    .ve-viewport-toggle button.active{background:#fff;color:#111;box-shadow:0 6px 16px rgba(61,125,131,.08)}
    .ve-snap-box{height:38px;display:inline-flex;align-items:center;gap:5px;border:1px solid var(--admin-line);border-radius:12px;background:#fff;padding:3px;color:#263237}
    .ve-snap-button{height:30px;border:0;border-radius:9px;background:#f8fbfa;color:#63747a;padding:0 9px;font-size:12px;font-weight:900;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
    .ve-snap-button i{font-size:16px}
    .ve-snap-button.active{background:#fff4e8;color:#ff7a1a}
    .ve-snap-size{height:30px;display:inline-flex;align-items:center;gap:6px;margin:0;padding:0 7px;border-left:1px solid var(--admin-line-2);color:#63747a;font-size:11px;font-weight:900}
    .ve-snap-size input{width:54px;height:28px;border-radius:9px;padding:5px 7px;font-size:12px}
    .ve-selected-card{border:1px solid var(--admin-line-2);border-radius:14px;background:#f8fbfa;padding:12px;margin-bottom:12px}
    .ve-selected-card strong{display:block;font-size:13px;font-weight:900;color:#111}
    .ve-selected-card code{display:block;margin-top:6px;color:#64777c;font-size:11px;font-weight:760;line-height:1.4;word-break:break-all;white-space:normal}
    .ve-action-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:14px}
    .ve-action-grid button{height:42px;border:1px solid var(--admin-line);border-radius:12px;background:#fff;color:#263237;display:grid;place-items:center;cursor:pointer;font-size:18px}
    .ve-action-grid button:hover{background:#fff6ed;border-color:#ffc790;color:#111}
    .ve-action-grid button.is-danger:hover{background:#fff1f2;border-color:#ffd0d5;color:#c94c5a}
    .ve-field{margin-bottom:12px}
    .ve-field label{display:block;margin-bottom:6px;color:#263237;font-size:12px;font-weight:900}
    .ve-field textarea{min-height:94px}
    .ve-field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .ve-section{border-top:1px solid var(--admin-line-2);padding-top:13px;margin-top:13px}
    .ve-block-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .ve-block-grid button{min-height:64px;border:1px solid var(--admin-line);border-radius:13px;background:#f8fbfa;color:#263237;padding:10px;display:grid;place-items:center;gap:5px;cursor:pointer;font-size:12px;font-weight:900}
    .ve-block-grid button i{font-size:20px;color:#718084}
    .ve-block-grid button:hover{background:#fff6ed;border-color:#ffc790}
    .ve-block-grid button:hover i{color:#ff7a1a}
    .page-modal{position:fixed;inset:0;background:rgba(15,23,42,.34);z-index:160;display:none;place-items:center;padding:24px;backdrop-filter:blur(8px)}
    .page-modal.show{display:grid}
    .page-modal-box{width:min(620px,100%);background:#fff;border:1px solid var(--admin-line);border-radius:20px;padding:26px;box-shadow:0 28px 90px rgba(17,24,39,.2)}
    .page-modal-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px}
    .page-modal-head h2{margin:0;font-size:22px;font-weight:900;letter-spacing:-.02em}
    .modal-close{width:42px;height:42px;border-radius:13px;border:1px solid var(--admin-line);background:#fff;color:#111;display:grid;place-items:center;font-size:20px;cursor:pointer}
    .modal-field{margin-bottom:14px}
    .modal-field label{display:block;margin-bottom:7px;font-size:12px;font-weight:900}
    .modal-help{font-size:12px;color:var(--admin-muted);font-weight:760;margin-top:6px;line-height:1.45}
    .menu-parent-box{display:none}
    .menu-parent-box.show{display:block}
    .modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:18px;border-top:1px solid var(--admin-line-2)}
    @media(max-width:1320px){body{overflow:auto}.ve-shell{min-height:auto}.ve-workspace{grid-template-columns:260px minmax(0,1fr);height:auto}.ve-workspace.tree-collapsed{grid-template-columns:64px minmax(0,1fr)}.ve-workspace.inspector-collapsed{grid-template-columns:260px minmax(0,1fr)}.ve-workspace.tree-collapsed.inspector-collapsed{grid-template-columns:64px minmax(0,1fr)}.ve-inspector{grid-column:1/-1}.ve-panel.ve-inspector{min-height:auto}.ve-frame-card{min-height:650px}}
    @media(max-width:900px){.ve-statusbar,.ve-canvas-toolbar{align-items:stretch;flex-direction:column}.ve-status-actions,.ve-toolbar-group{width:100%;justify-content:flex-start}.ve-page-picker{width:100%}.ve-page-picker select{width:100%}.ve-workspace,.ve-workspace.tree-collapsed,.ve-workspace.inspector-collapsed,.ve-workspace.tree-collapsed.inspector-collapsed{grid-template-columns:1fr}.ve-frame-card{min-height:560px}.ve-action-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
</head>
<body>
<div class="ve-shell">
    <div class="ve-statusbar">
        <div class="ve-status" id="saveStatus">
            <i class="ti ti-circle-check"></i>
            <span>Hazırdır. Obyekt seç, düzəliş et və public sayta göndərmək üçün yadda saxla.</span>
        </div>
        <div class="ve-status-actions">
            <label class="ve-page-picker" title="Redaktə ediləcək səhifə">
                <i class="ti ti-file-text"></i>
                <select id="pageSelect" aria-label="Səhifə seçimi">
                    @foreach($pages as $targetUrl => $label)
                        <option value="{{ route('admin.visual-editor.index', ['lang_code' => $selectedLanguage, 'target' => $targetUrl]) }}" @selected($targetUrl === $target)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <a class="btn btn-light" href="{{ route('admin.dashboard') }}"><i class="ti ti-arrow-left"></i> Admin panel</a>
            <button class="btn btn-primary" type="button" id="btnSaveAll"><i class="ti ti-device-floppy"></i> Yadda saxla</button>
        </div>
    </div>

    <div class="ve-workspace">
        <aside class="ve-panel ve-object-panel">
            <div class="ve-panel-head">
                <div>
                    <h2>Obyekt ağacı</h2>
                    <span>Səhifədəki elementi seç və idarə et</span>
                </div>
                <button class="ve-tool-btn ve-tree-toggle" type="button" id="treeToggle" title="Obyekt ağacını yığ/aç" aria-expanded="true"><i class="ti ti-layout-sidebar-left-collapse"></i></button>
            </div>
            <div class="ve-panel-body">
                <p class="ve-mini-title">Obyekt ağacı</p>
                <div class="ve-tree" id="objectTree">
                    <div class="ve-tree-empty">Səhifə yüklənəndən sonra obyekt ağacı burada görünəcək.</div>
                </div>
            </div>
        </aside>

        <section class="ve-panel ve-canvas">
            <div class="ve-canvas-toolbar">
                <div class="ve-toolbar-group">
                    <button class="ve-tool-btn" type="button" id="btnUndo" title="Geri al"><i class="ti ti-arrow-back-up"></i></button>
                    <button class="ve-tool-btn" type="button" id="btnRedo" title="İrəli qaytar"><i class="ti ti-arrow-forward-up"></i></button>
                    <button class="ve-tool-btn" type="button" id="reloadFrameBtn" title="Yenilə"><i class="ti ti-refresh"></i></button>
                    <button class="ve-tool-btn is-danger" type="button" id="btnResetPage" title="Səhifəni sıfırla"><i class="ti ti-eraser"></i></button>
                </div>
                <div class="ve-toolbar-group">
                    <div class="ve-viewport-toggle" aria-label="Ekran ölçüsü">
                        <button type="button" class="active" data-viewport="desktop"><i class="ti ti-device-desktop"></i> Desktop</button>
                        <button type="button" data-viewport="mobile"><i class="ti ti-device-mobile"></i> Mobile</button>
                    </div>
                    <div class="ve-snap-box" title="Maqnitləşmə">
                        <input type="checkbox" id="snapToggle" hidden>
                        <button class="ve-snap-button" type="button" id="snapButton" aria-pressed="false"><i class="ti ti-magnet"></i> Snap</button>
                        <label class="ve-snap-size" for="snapSize">
                            <span>Grid</span>
                            <input type="number" id="snapSize" min="2" max="80" step="2" value="12" aria-label="Snap ölçüsü">
                        </label>
                    </div>
                </div>
            </div>

            <div class="ve-frame-wrap">
                <div class="ve-frame-card viewport-desktop">
                    <div class="ve-frame-head">
                        <strong>{{ $pages[$target] ?? $target }}</strong>
                        <span>{{ $target }} · page_key: {{ $pageKey }}</span>
                    </div>
                    <iframe id="liveFrame" class="ve-frame" src="{{ $previewUrl }}"></iframe>
                </div>
            </div>
        </section>

        <aside class="ve-panel ve-inspector">
            <div class="ve-panel-head">
                <div>
                    <h2>Seçilən obyekt</h2>
                    <span>Copy, delete, resize və dəqiq ölçülər</span>
                </div>
                <button class="ve-tool-btn ve-inspector-toggle" type="button" id="inspectorToggle" title="Seçilən obyekt panelini yığ/aç" aria-expanded="true"><i class="ti ti-layout-sidebar-right-collapse"></i></button>
            </div>
            <div class="ve-panel-body">
                <div class="ve-selected-card">
                    <strong id="selectedLabel">Obyekt seçilməyib</strong>
                    <code id="selectedInfo">Səhifədə obyektə klik et və ya soldakı ağacdan seç.</code>
                </div>

                <div class="ve-action-grid">
                    <button type="button" id="btnCopy" title="Copy"><i class="ti ti-copy"></i></button>
                    <button type="button" id="btnPaste" title="Paste"><i class="ti ti-clipboard"></i></button>
                    <button type="button" id="btnDuplicate" title="Dublikat"><i class="ti ti-copy-plus"></i></button>
                    <button type="button" class="is-danger" id="btnDelete" title="Sil / gizlət"><i class="ti ti-trash"></i></button>
                </div>

                <div class="ve-field-grid">
                    <div class="ve-field">
                        <label>X</label>
                        <input type="number" id="dimX" step="1" placeholder="0">
                    </div>
                    <div class="ve-field">
                        <label>Y</label>
                        <input type="number" id="dimY" step="1" placeholder="0">
                    </div>
                    <div class="ve-field">
                        <label>En</label>
                        <input type="number" id="dimW" step="1" placeholder="auto">
                    </div>
                    <div class="ve-field">
                        <label>Hündürlük</label>
                        <input type="number" id="dimH" step="1" placeholder="auto">
                    </div>
                </div>
                <button class="btn btn-light" type="button" id="btnApplyBox" style="width:100%;margin-bottom:12px"><i class="ti ti-ruler-measure"></i> Ölçüləri tətbiq et</button>

                <div class="ve-field">
                    <label>Mətn / HTML</label>
                    <textarea id="editText" placeholder="Seçilən obyektin mətni..."></textarea>
                </div>
                <div class="ve-field-grid">
                    <button class="btn btn-primary" type="button" id="btnApplyText"><i class="ti ti-text-size"></i> Mətn</button>
                    <button class="btn btn-light" type="button" id="btnApplyHtml"><i class="ti ti-code"></i> HTML</button>
                </div>

                <div class="ve-field" style="margin-top:12px">
                    <label>Link</label>
                    <input type="text" id="editLink" placeholder="# və ya /page?key=...">
                </div>
                <button class="btn btn-light" type="button" id="btnApplyLink" style="width:100%"><i class="ti ti-link"></i> Linki tətbiq et</button>

                <div class="ve-section">
                    <p class="ve-mini-title">Stil</p>
                    <div class="ve-field-grid">
                        <div class="ve-field">
                            <label>Yazı rəngi</label>
                            <input type="color" id="styleColor" value="#071728">
                        </div>
                        <div class="ve-field">
                            <label>Fon rəngi</label>
                            <input type="color" id="styleBg" value="#ffffff">
                        </div>
                        <div class="ve-field">
                            <label>Font</label>
                            <input type="text" id="styleFont" placeholder="24px">
                        </div>
                        <div class="ve-field">
                            <label>Radius</label>
                            <input type="text" id="styleRadius" placeholder="16px">
                        </div>
                        <div class="ve-field">
                            <label>Padding</label>
                            <input type="text" id="stylePadding" placeholder="20px">
                        </div>
                        <div class="ve-field">
                            <label>Margin</label>
                            <input type="text" id="styleMargin" placeholder="20px 0">
                        </div>
                        <div class="ve-field">
                            <label>Rotate</label>
                            <input type="text" id="styleRotate" placeholder="0deg">
                        </div>
                        <div class="ve-field">
                            <label>Kölgə</label>
                            <select id="styleShadow">
                                <option value="">Dəyişmə</option>
                                <option value="none">Yoxdur</option>
                                <option value="0 18px 55px rgba(7,23,40,.12)">Yumşaq</option>
                                <option value="0 28px 90px rgba(7,23,40,.22)">Güclü</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="button" id="btnApplyStyle" style="width:100%"><i class="ti ti-palette"></i> Stili tətbiq et</button>
                </div>

                <div class="ve-section">
                    <p class="ve-mini-title">Şəkil</p>
                    <div class="ve-field">
                        <input type="file" id="imageUpload" accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
                    </div>
                </div>

                <div class="ve-section">
                    <p class="ve-mini-title">Yeni blok</p>
                    <div class="ve-block-grid">
                        <button type="button" data-tool="heading"><i class="ti ti-heading"></i>Başlıq</button>
                        <button type="button" data-tool="text"><i class="ti ti-align-left"></i>Mətn</button>
                        <button type="button" data-tool="image"><i class="ti ti-photo"></i>Şəkil</button>
                        <button type="button" data-tool="button"><i class="ti ti-square-arrow-right"></i>Düymə</button>
                        <button type="button" data-tool="alert"><i class="ti ti-bell"></i>Bildiriş</button>
                        <button type="button" data-tool="divider"><i class="ti ti-minus"></i>Xətt</button>
                        <button type="button" data-tool="spacer"><i class="ti ti-arrows-vertical"></i>Boşluq</button>
                        <button type="button" data-tool="group"><i class="ti ti-layout-grid"></i>Qrup</button>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

@php
    $existingEditsPayload = $existingEdits->map(function ($item) {
        return [
            'selector' => $item->selector,
            'edit_type' => $item->edit_type,
            'edit_value' => $item->edit_value,
            'page_key' => $item->page_key,
        ];
    })->values();

    $existingBlocksPayload = $existingBlocks->map(function ($item) {
        return [
            'target_selector' => $item->target_selector,
            'block_html' => $item->block_html,
            'sort_order' => $item->sort_order,
            'page_key' => $item->page_key,
        ];
    })->values();
@endphp

<script>
(function(){
    const iframe = document.getElementById('liveFrame');
    const CONFIG = {
        pageKey: @json($pageKey),
        langCode: @json($selectedLanguage),
        apiUrl: @json($apiUrl),
        edits: {!! \Illuminate\Support\Js::from($existingEditsPayload) !!},
        blocks: {!! \Illuminate\Support\Js::from($existingBlocksPayload) !!},
        currentTarget: @json($target),
        csrf: @json(csrf_token())
    };

    let frameWin = null;
    let frameDoc = null;
    let selectedEl = null;
    let selectedSelector = '';
    let clipboardHtml = '';
    let pendingEdits = new Map();
    let pendingBlocks = [];
    let undoStack = [];
    let redoStack = [];
    let treeNodeMap = new Map();
    let snapEnabled = false;
    let snapSize = 12;

    const saveStatus = document.getElementById('saveStatus');
    const selectedLabel = document.getElementById('selectedLabel');
    const selectedInfo = document.getElementById('selectedInfo');
    const objectTree = document.getElementById('objectTree');
    const editText = document.getElementById('editText');
    const editLink = document.getElementById('editLink');
    const btnUndo = document.getElementById('btnUndo');
    const btnRedo = document.getElementById('btnRedo');
    const snapToggle = document.getElementById('snapToggle');
    const snapButton = document.getElementById('snapButton');
    const snapSizeInput = document.getElementById('snapSize');
    const workspace = document.querySelector('.ve-workspace');
    const treeToggle = document.getElementById('treeToggle');
    const inspectorToggle = document.getElementById('inspectorToggle');
    const pageSelect = document.getElementById('pageSelect');
    const initialPageSelectValue = pageSelect ? pageSelect.value : '';
    const frameCard = document.querySelector('.ve-frame-card');

    function setStatus(type, message) {
        saveStatus.className = 've-status' + (type ? ' ' + type : '');
        const icon = type === 'dirty' ? 'ti-alert-triangle' : (type === 'error' ? 'ti-alert-circle' : 'ti-circle-check');
        saveStatus.innerHTML = '<i class="ti ' + icon + '"></i><span>' + message + '</span>';
    }

    function setDirty() {
        const count = pendingEdits.size + pendingBlocks.length;
        if (count > 0) {
            setStatus('dirty', count + ' dəyişiklik gözləyir. Public sayta düşməsi üçün yadda saxla.');
        } else {
            setStatus('', 'Dəyişiklik yoxdur. Hər şey yadda saxlanılıb.');
        }
        updateHistoryButtons();
    }

    function updateHistoryButtons() {
        btnUndo.disabled = undoStack.length === 0;
        btnRedo.disabled = redoStack.length === 0;
    }

    function cleanSnapshotHtml() {
        if (!frameDoc) return '';
        const clone = frameDoc.body.cloneNode(true);
        clone.querySelectorAll('#veSelectionOverlay, #veNote, #veSnapGrid').forEach(el => el.remove());
        clone.querySelectorAll('.ve-hover, .ve-selected-real').forEach(el => el.classList.remove('ve-hover', 've-selected-real'));
        clone.classList.remove('ve-snap-enabled');
        return clone.innerHTML;
    }

    function captureState(label) {
        return {
            label: label || 'Dəyişiklik',
            bodyHtml: cleanSnapshotHtml(),
            selectedSelector,
            pendingEdits: Array.from(pendingEdits.values()),
            pendingBlocks: JSON.parse(JSON.stringify(pendingBlocks))
        };
    }

    function pushUndo(label) {
        if (!frameDoc) return;
        undoStack.push(captureState(label));
        if (undoStack.length > 60) undoStack.shift();
        redoStack = [];
        updateHistoryButtons();
    }

    function restoreState(state, message) {
        if (!frameDoc || !state) return;
        frameDoc.body.innerHTML = state.bodyHtml;
        pendingEdits = new Map();
        (state.pendingEdits || []).forEach(edit => pendingEdits.set(edit.selector + '|' + edit.edit_type, edit));
        pendingBlocks = state.pendingBlocks || [];
        selectedEl = null;
        selectedSelector = '';
        resetSelectionPanel();
        injectSelectionOverlay(true);
        updateSnapGrid();
        buildObjectTree();
        setDirty();
        note(message || 'Bərpa olundu.');
    }

    function resetSelectionPanel() {
        selectedLabel.textContent = 'Obyekt seçilməyib';
        selectedInfo.textContent = 'Səhifədə obyektə klik et və ya soldakı ağacdan seç.';
        editText.value = '';
        editLink.value = '';
        ['dimX','dimY','dimW','dimH','styleFont','styleRadius','stylePadding','styleMargin','styleRotate'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });
        document.querySelectorAll('.ve-tree-row.active').forEach(row => row.classList.remove('active'));
    }

    iframe.addEventListener('load', () => {
        try {
            frameWin = iframe.contentWindow;
            frameDoc = iframe.contentDocument || frameWin.document;
            initFrameEditor();
        } catch (e) {
            setStatus('error', 'Live editor işə düşmədi: ' + e.message);
        }
    });

    function initFrameEditor() {
        if (!frameDoc || !frameDoc.body) throw new Error('Iframe document tapılmadı.');
        selectedEl = null;
        selectedSelector = '';
        pendingEdits.clear();
        pendingBlocks = [];
        undoStack = [];
        redoStack = [];

        injectFrameStyle();
        applySavedBlocks();
        applySavedEdits();
        injectSelectionOverlay(false);
        updateSnapGrid();
        bindFrameEvents();
        buildObjectTree();
        resetSelectionPanel();
        setDirty();
    }

    function bindFrameEvents() {
        frameDoc.addEventListener('mouseover', function(e){
            if (isEditorElement(e.target) || isProtectedElement(e.target)) return;
            e.target.classList.add('ve-hover');
        }, true);

        frameDoc.addEventListener('mouseout', function(e){
            if (isEditorElement(e.target)) return;
            e.target.classList.remove('ve-hover');
        }, true);

        frameDoc.addEventListener('click', function(e){
            if (isEditorElement(e.target) || isProtectedElement(e.target)) return;
            e.preventDefault();
            e.stopPropagation();
            selectElement(e.target);
        }, true);

        frameDoc.addEventListener('scroll', updateOverlay, true);
        frameWin.addEventListener('resize', updateOverlay);
    }

    function isProtectedElement(el) {
        if (!el || !el.closest) return true;
        return !!el.closest('#veSelectionOverlay,#veNote,#veSnapGrid,script,style,meta,link,title,header,.site-header,footer,.site-footer,.auth-backdrop,.site-search,.site-search-backdrop');
    }

    function isEditorElement(el) {
        return !!(el && el.closest && (el.closest('#veSelectionOverlay') || el.closest('#veNote') || el.closest('#veSnapGrid')));
    }

    function injectFrameStyle() {
        if (frameDoc.getElementById('veEditorInternalStyle')) return;
        const style = frameDoc.createElement('style');
        style.id = 'veEditorInternalStyle';
        style.innerHTML = `
            .ve-hover{outline:2px dashed rgba(255,122,26,.85)!important;outline-offset:3px!important;cursor:pointer!important}
            .ve-selected-real{outline:3px solid #ff7a1a!important;outline-offset:4px!important}
            #veSnapGrid{position:fixed;inset:0;z-index:2147481900;pointer-events:none;display:none;background-image:linear-gradient(rgba(255,122,26,.07) 1px,transparent 1px),linear-gradient(90deg,rgba(255,122,26,.07) 1px,transparent 1px);background-size:var(--ve-snap-size,12px) var(--ve-snap-size,12px)}
            body.ve-snap-enabled #veSnapGrid{display:block}
            #veSelectionOverlay{position:absolute;z-index:2147482000;display:none;pointer-events:none;border:2px solid #ff7a1a;border-radius:9px;box-shadow:0 0 0 99999px rgba(7,23,40,.025)}
            #veSelectionOverlay .ve-handle{position:absolute;width:14px;height:14px;background:#fff;border:2px solid #ff7a1a;border-radius:50%;pointer-events:auto;box-shadow:0 4px 14px rgba(0,0,0,.18)}
            #veSelectionOverlay .ve-move{left:50%;top:50%;transform:translate(-50%,-50%);width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:#ff7a1a;color:#fff;pointer-events:auto;cursor:move;font-size:18px;font-family:Arial,sans-serif}
            #veSelectionOverlay .nw{left:-8px;top:-8px;cursor:nwse-resize}
            #veSelectionOverlay .ne{right:-8px;top:-8px;cursor:nesw-resize}
            #veSelectionOverlay .sw{left:-8px;bottom:-8px;cursor:nesw-resize}
            #veSelectionOverlay .se{right:-8px;bottom:-8px;cursor:nwse-resize}
            #veSelectionOverlay .rotate{left:50%;top:-42px;transform:translateX(-50%);width:18px;height:18px;background:#111;border-color:#111;cursor:grab}
            #veSelectionOverlay .ve-rotate-line{position:absolute;left:50%;top:-25px;width:2px;height:25px;transform:translateX(-50%);background:#111}
            #veSelectionOverlay .ve-object-toolbar{position:absolute;left:0;top:-47px;display:flex;align-items:center;gap:5px;padding:5px;border:1px solid rgba(255,255,255,.18);border-radius:13px;background:#071728;box-shadow:0 16px 44px rgba(7,23,40,.22);pointer-events:auto}
            #veSelectionOverlay .ve-object-toolbar button{width:31px;height:31px;border:0;border-radius:9px;background:rgba(255,255,255,.08);color:#fff;display:grid;place-items:center;cursor:pointer;font-size:14px}
            #veSelectionOverlay .ve-object-toolbar button:hover{background:#ff7a1a;color:#fff}
            #veSelectionOverlay .ve-object-toolbar button.danger:hover{background:#c94c5a}
            .ve-note{position:fixed;right:14px;bottom:14px;z-index:2147483003;background:#071728;color:#fff;border-radius:12px;padding:11px 13px;box-shadow:0 16px 50px rgba(7,23,40,.22);display:none;font-family:Arial,sans-serif;font-size:12px;font-weight:800}
            .ve-added-block{animation:.45s ease both}.ve-anim-up{animation-name:veUp}@keyframes veUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
            .ve-tool-box{margin:24px auto;max-width:1180px;background:#fff;border:1px solid #dbe4ee;border-radius:22px;padding:28px;box-shadow:0 18px 55px rgba(7,23,40,.07);color:#071728}
            .ve-tool-title{margin:0 0 10px;font-size:28px;line-height:1.2;font-weight:900;color:#071728}
            .ve-tool-text{margin:0;font-size:16px;line-height:1.75;color:#334155;font-weight:650}
            .ve-tool-btn{margin-top:16px;display:inline-flex;align-items:center;gap:8px;background:#ff7a1a;color:#fff;padding:11px 16px;border-radius:12px;text-decoration:none;font-weight:900}
            .ve-tool-img{width:100%;max-height:360px;object-fit:cover;border-radius:18px;margin-top:16px}
            .ve-tool-divider{height:1px;background:#dbe4ee;margin:28px auto;max-width:1180px}
            .ve-tool-spacer{height:50px}
        `;
        frameDoc.head.appendChild(style);
    }

    function injectSelectionOverlay(force) {
        const oldOverlay = frameDoc.getElementById('veSelectionOverlay');
        if (oldOverlay && force) oldOverlay.remove();

        if (!frameDoc.getElementById('veSnapGrid')) {
            const grid = frameDoc.createElement('div');
            grid.id = 'veSnapGrid';
            frameDoc.body.appendChild(grid);
        }

        if (!frameDoc.getElementById('veSelectionOverlay')) {
            const overlay = frameDoc.createElement('div');
            overlay.id = 'veSelectionOverlay';
            overlay.innerHTML = `
                <div class="ve-object-toolbar">
                    <button type="button" data-action="copy" title="Copy">⧉</button>
                    <button type="button" data-action="duplicate" title="Dublikat">＋</button>
                    <button type="button" data-action="front" title="Önə gətir">↑</button>
                    <button type="button" data-action="back" title="Arxaya apar">↓</button>
                    <button type="button" class="danger" data-action="delete" title="Sil">×</button>
                </div>
                <div class="ve-rotate-line"></div>
                <div class="ve-handle rotate" data-action="rotate"></div>
                <div class="ve-handle nw" data-action="resize-nw"></div>
                <div class="ve-handle ne" data-action="resize-ne"></div>
                <div class="ve-handle sw" data-action="resize-sw"></div>
                <div class="ve-handle se" data-action="resize-se"></div>
                <div class="ve-move" data-action="move">↕</div>
            `;
            frameDoc.body.appendChild(overlay);
            overlay.querySelectorAll('[data-action]').forEach(handle => {
                handle.addEventListener('mousedown', startOverlayAction, true);
                handle.addEventListener('click', handleOverlayClick, true);
            });
        }

        if (!frameDoc.getElementById('veNote')) {
            const noteBox = frameDoc.createElement('div');
            noteBox.className = 've-note';
            noteBox.id = 'veNote';
            frameDoc.body.appendChild(noteBox);
        }
    }

    function handleOverlayClick(e) {
        const action = e.currentTarget.getAttribute('data-action');
        if (['copy','duplicate','delete','front','back'].includes(action)) {
            e.preventDefault();
            e.stopPropagation();
            runObjectAction(action);
        }
    }

    function runObjectAction(action) {
        if (!selectedEl) return;
        if (action === 'copy') {
            clipboardHtml = selectedEl.outerHTML;
            note('Obyekt kopyalandı.');
            return;
        }
        if (action === 'duplicate') {
            duplicateSelected();
            return;
        }
        if (action === 'delete') {
            hideSelected();
            return;
        }
        if (action === 'front' || action === 'back') {
            pushUndo(action === 'front' ? 'Önə gətir' : 'Arxaya apar');
            selectedEl.style.position = selectedEl.style.position && selectedEl.style.position !== 'static' ? selectedEl.style.position : 'relative';
            selectedEl.style.zIndex = action === 'front' ? '999' : '1';
            queueEdit(selectedEl, selectedSelector, 'style', JSON.stringify(collectInlineStyle(selectedEl)), false);
            syncStyleInputs();
            note(action === 'front' ? 'Obyekt önə gətirildi.' : 'Obyekt arxaya aparıldı.');
        }
    }

    function selectElement(el) {
        if (!el || isProtectedElement(el)) return;
        if (selectedEl) selectedEl.classList.remove('ve-selected-real');
        selectedEl = el;
        selectedSelector = getSelector(el);
        selectedEl.classList.add('ve-selected-real');

        selectedLabel.textContent = elementLabel(el);
        selectedInfo.textContent = selectedSelector || 'Selector tapılmadı';
        editText.value = selectedEl.innerText || selectedEl.textContent || '';
        const linkEl = selectedEl.closest('a');
        editLink.value = linkEl ? (linkEl.getAttribute('href') || '') : (selectedEl.getAttribute('href') || '');
        syncStyleInputs();
        syncBoxInputs();
        markTreeActive();
        updateOverlay();
    }

    function elementLabel(el) {
        const tag = el.tagName ? el.tagName.toLowerCase() : 'element';
        const id = el.id ? '#' + el.id : '';
        const cls = typeof el.className === 'string' ? el.className.split(/\s+/).filter(c => c && !c.startsWith('ve-')).slice(0,2).map(c => '.' + c).join('') : '';
        return tag + id + cls;
    }

    function updateOverlay() {
        if (!frameDoc) return;
        const overlay = frameDoc.getElementById('veSelectionOverlay');
        if (!overlay || !selectedEl) {
            if (overlay) overlay.style.display = 'none';
            return;
        }
        if (!frameDoc.body.contains(selectedEl)) {
            selectedEl = null;
            selectedSelector = '';
            overlay.style.display = 'none';
            resetSelectionPanel();
            return;
        }
        const rect = selectedEl.getBoundingClientRect();
        const scrollX = frameWin.scrollX || frameDoc.documentElement.scrollLeft || 0;
        const scrollY = frameWin.scrollY || frameDoc.documentElement.scrollTop || 0;
        overlay.style.display = 'block';
        overlay.style.left = (rect.left + scrollX) + 'px';
        overlay.style.top = (rect.top + scrollY) + 'px';
        overlay.style.width = Math.max(20, rect.width) + 'px';
        overlay.style.height = Math.max(20, rect.height) + 'px';
        syncBoxInputs();
    }

    function startOverlayAction(e) {
        const action = e.currentTarget.getAttribute('data-action');
        if (!selectedEl || ['copy','duplicate','delete','front','back'].includes(action)) return;

        e.preventDefault();
        e.stopPropagation();
        pushUndo(action);

        const startX = e.clientX;
        const startY = e.clientY;
        const rect = selectedEl.getBoundingClientRect();
        const baseWidth = rect.width;
        const baseHeight = rect.height;
        const baseLeft = parseFloat(selectedEl.style.left || '0') || 0;
        const baseTop = parseFloat(selectedEl.style.top || '0') || 0;
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        if (!selectedEl.style.position || selectedEl.style.position === 'static') selectedEl.style.position = 'relative';
        selectedEl.style.zIndex = selectedEl.style.zIndex || '50';

        function onMove(ev) {
            ev.preventDefault();
            const dx = ev.clientX - startX;
            const dy = ev.clientY - startY;

            if (action === 'move') {
                selectedEl.style.left = snap(baseLeft + dx) + 'px';
                selectedEl.style.top = snap(baseTop + dy) + 'px';
            }

            if (action.startsWith('resize')) {
                let newW = baseWidth;
                let newH = baseHeight;
                if (action.includes('e')) newW = baseWidth + dx;
                if (action.includes('s')) newH = baseHeight + dy;
                if (action.includes('w')) newW = baseWidth - dx;
                if (action.includes('n')) newH = baseHeight - dy;
                selectedEl.style.width = Math.max(30, snap(newW)) + 'px';
                selectedEl.style.height = Math.max(20, snap(newH)) + 'px';
            }

            if (action === 'rotate') {
                const angle = Math.atan2(ev.clientY - centerY, ev.clientX - centerX) * 180 / Math.PI;
                selectedEl.style.transform = 'rotate(' + Math.round(angle + 90) + 'deg)';
            }

            syncStyleInputs();
            syncBoxInputs();
            updateOverlay();
        }

        function onUp() {
            frameDoc.removeEventListener('mousemove', onMove, true);
            frameDoc.removeEventListener('mouseup', onUp, true);
            queueEdit(selectedEl, selectedSelector, 'style', JSON.stringify(collectInlineStyle(selectedEl)), false);
            buildObjectTree();
            note('Dəyişiklik tətbiq olundu. Public üçün yadda saxla.');
        }

        frameDoc.addEventListener('mousemove', onMove, true);
        frameDoc.addEventListener('mouseup', onUp, true);
    }

    function snap(value) {
        if (!snapEnabled || !snapSize) return Math.round(value);
        return Math.round(value / snapSize) * snapSize;
    }

    function updateSnapGrid() {
        snapEnabled = !!snapToggle.checked;
        snapSize = Math.max(2, parseInt(snapSizeInput.value || '8', 10));
        if (snapButton) {
            snapButton.classList.toggle('active', snapEnabled);
            snapButton.setAttribute('aria-pressed', snapEnabled ? 'true' : 'false');
        }
        if (!frameDoc || !frameDoc.body) return;
        frameDoc.body.classList.toggle('ve-snap-enabled', snapEnabled);
        frameDoc.body.style.setProperty('--ve-snap-size', snapSize + 'px');
    }

    snapButton.addEventListener('click', () => {
        snapToggle.checked = !snapToggle.checked;
        updateSnapGrid();
    });
    snapToggle.addEventListener('change', updateSnapGrid);
    snapSizeInput.addEventListener('change', updateSnapGrid);

    function syncBoxInputs() {
        if (!selectedEl) return;
        const rect = selectedEl.getBoundingClientRect();
        document.getElementById('dimX').value = parseInt(selectedEl.style.left || '0', 10) || 0;
        document.getElementById('dimY').value = parseInt(selectedEl.style.top || '0', 10) || 0;
        document.getElementById('dimW').value = Math.round(rect.width) || '';
        document.getElementById('dimH').value = Math.round(rect.height) || '';
    }

    function syncStyleInputs() {
        if (!selectedEl) return;
        document.getElementById('styleColor').value = rgbToHex(selectedEl.style.color) || '#071728';
        document.getElementById('styleBg').value = rgbToHex(selectedEl.style.backgroundColor) || '#ffffff';
        document.getElementById('styleFont').value = selectedEl.style.fontSize || '';
        document.getElementById('styleRadius').value = selectedEl.style.borderRadius || '';
        document.getElementById('stylePadding').value = selectedEl.style.padding || '';
        document.getElementById('styleMargin').value = selectedEl.style.margin || '';
        document.getElementById('styleRotate').value = getCurrentRotate(selectedEl) ? getCurrentRotate(selectedEl) + 'deg' : '';
    }

    function rgbToHex(value) {
        if (!value) return '';
        if (value.startsWith('#')) return value;
        const m = value.match(/\d+/g);
        if (!m || m.length < 3) return '';
        return '#' + m.slice(0,3).map(x => {
            const h = parseInt(x,10).toString(16);
            return h.length === 1 ? '0' + h : h;
        }).join('');
    }

    function getCurrentRotate(el) {
        const t = el.style.transform || '';
        const m = t.match(/rotate\(([-0-9.]+)deg\)/);
        return m ? parseFloat(m[1]) : 0;
    }

    function collectStyleFromInputs() {
        const st = collectInlineStyle(selectedEl);
        const color = document.getElementById('styleColor').value;
        const bg = document.getElementById('styleBg').value;
        const font = document.getElementById('styleFont').value.trim();
        const radius = document.getElementById('styleRadius').value.trim();
        const padding = document.getElementById('stylePadding').value.trim();
        const margin = document.getElementById('styleMargin').value.trim();
        const rotate = document.getElementById('styleRotate').value.trim();
        const shadow = document.getElementById('styleShadow').value;
        if (color) st.color = color;
        if (bg) st.backgroundColor = bg;
        if (font) st.fontSize = font;
        if (radius) st.borderRadius = radius;
        if (padding) st.padding = padding;
        if (margin) st.margin = margin;
        if (rotate) st.transform = 'rotate(' + rotate.replace('deg','') + 'deg)';
        if (shadow) st.boxShadow = shadow;
        return st;
    }

    function collectInlineStyle(el) {
        const keys = ['color','backgroundColor','fontSize','borderRadius','padding','boxShadow','margin','fontWeight','textAlign','lineHeight','letterSpacing','width','height','minHeight','maxWidth','opacity','position','left','top','right','bottom','zIndex','transform'];
        const obj = {};
        keys.forEach(k => {
            if (el && el.style[k]) obj[k] = el.style[k];
        });
        return obj;
    }

    function queueEdit(el, selector, type, value, apply = true) {
        if (!el || !selector || !type) return;
        if (apply) applyLocal(el, type, value);
        pendingEdits.set(selector + '|' + type, { selector, edit_type: type, edit_value: value });
        setDirty();
        updateOverlay();
        markTreeActive();
    }

    document.getElementById('btnApplyText').addEventListener('click', () => {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        pushUndo('Mətn');
        queueEdit(selectedEl, selectedSelector, 'text', editText.value);
        buildObjectTree();
        note('Mətn tətbiq olundu.');
    });

    document.getElementById('btnApplyHtml').addEventListener('click', () => {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        pushUndo('HTML');
        queueEdit(selectedEl, selectedSelector, 'html', editText.value);
        buildObjectTree();
        note('HTML tətbiq olundu.');
    });

    document.getElementById('btnApplyLink').addEventListener('click', () => {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        pushUndo('Link');
        const linkEl = selectedEl.closest('a') || selectedEl;
        queueEdit(linkEl, getSelector(linkEl), 'href', editLink.value || '#');
        note('Link tətbiq olundu.');
    });

    document.getElementById('btnApplyBox').addEventListener('click', () => {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        pushUndo('Ölçü');
        selectedEl.style.position = selectedEl.style.position && selectedEl.style.position !== 'static' ? selectedEl.style.position : 'relative';
        const x = document.getElementById('dimX').value;
        const y = document.getElementById('dimY').value;
        const w = document.getElementById('dimW').value;
        const h = document.getElementById('dimH').value;
        if (x !== '') selectedEl.style.left = snap(parseInt(x,10) || 0) + 'px';
        if (y !== '') selectedEl.style.top = snap(parseInt(y,10) || 0) + 'px';
        if (w !== '') selectedEl.style.width = Math.max(30, snap(parseInt(w,10) || 0)) + 'px';
        if (h !== '') selectedEl.style.height = Math.max(20, snap(parseInt(h,10) || 0)) + 'px';
        queueEdit(selectedEl, selectedSelector, 'style', JSON.stringify(collectInlineStyle(selectedEl)), false);
        updateOverlay();
        note('Ölçülər tətbiq olundu.');
    });

    document.getElementById('btnApplyStyle').addEventListener('click', () => {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        pushUndo('Stil');
        queueEdit(selectedEl, selectedSelector, 'style', JSON.stringify(collectStyleFromInputs()));
        note('Stil tətbiq olundu.');
    });

    document.getElementById('btnDelete').addEventListener('click', hideSelected);
    document.getElementById('btnCopy').addEventListener('click', () => {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        clipboardHtml = selectedEl.outerHTML;
        note('Obyekt kopyalandı.');
    });
    document.getElementById('btnPaste').addEventListener('click', pasteClipboard);
    document.getElementById('btnDuplicate').addEventListener('click', duplicateSelected);

    function hideSelected() {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        if (!confirm('Bu obyekt gizlənsin?')) return;
        pushUndo('Gizlət');
        queueEdit(selectedEl, selectedSelector, 'hide', '1');
        selectedEl = null;
        selectedSelector = '';
        updateOverlay();
        buildObjectTree();
        resetSelectionPanel();
        note('Obyekt gizlədildi.');
    }

    function pasteClipboard() {
        if (!clipboardHtml) return alert('Kopyalanmış obyekt yoxdur.');
        pushUndo('Paste');
        const target = selectedEl ? selectedEl.parentElement : (frameDoc.querySelector('main') || frameDoc.body);
        appendBlockLocally(clipboardHtml, getSelector(target));
    }

    function duplicateSelected() {
        if (!selectedEl) return alert('Obyekt seçilməyib.');
        pushUndo('Dublikat');
        appendBlockLocally(selectedEl.outerHTML, getSelector(selectedEl.parentElement || frameDoc.body));
    }

    btnUndo.addEventListener('click', () => {
        const item = undoStack.pop();
        if (!item) return note('Geri alınacaq əməliyyat yoxdur.');
        redoStack.push(captureState('Redo'));
        restoreState(item, 'Geri alındı.');
    });

    btnRedo.addEventListener('click', () => {
        const item = redoStack.pop();
        if (!item) return note('Redo əməliyyatı yoxdur.');
        undoStack.push(captureState('Undo'));
        restoreState(item, 'İrəli qaytarıldı.');
    });

    document.getElementById('reloadFrameBtn').addEventListener('click', () => {
        if (pendingEdits.size || pendingBlocks.length) {
            if (!confirm('Yadda saxlanmamış dəyişikliklər var. Yenilənsə, onlar itəcək. Davam edilsin?')) return;
        }
        iframe.contentWindow.location.reload();
    });

    if (pageSelect) {
        pageSelect.addEventListener('change', function(){
            if (pendingEdits.size || pendingBlocks.length) {
                if (!confirm('Yadda saxlanmamış dəyişikliklər var. Səhifə dəyişsə, onlar itəcək. Davam edilsin?')) {
                    this.value = initialPageSelectValue;
                    return;
                }
            }
            window.location.href = this.value;
        });
    }

    if (treeToggle && workspace) {
        treeToggle.addEventListener('click', () => {
            const collapsed = workspace.classList.toggle('tree-collapsed');
            treeToggle.classList.toggle('is-collapsed', collapsed);
            treeToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            window.setTimeout(updateOverlay, 80);
        });
    }

    if (inspectorToggle && workspace) {
        inspectorToggle.addEventListener('click', () => {
            const collapsed = workspace.classList.toggle('inspector-collapsed');
            inspectorToggle.classList.toggle('is-collapsed', collapsed);
            inspectorToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            window.setTimeout(updateOverlay, 80);
        });
    }

    document.querySelectorAll('[data-viewport]').forEach(button => {
        button.addEventListener('click', () => {
            const mode = button.getAttribute('data-viewport') || 'desktop';
            document.querySelectorAll('[data-viewport]').forEach(item => item.classList.toggle('active', item === button));
            if (frameCard) {
                frameCard.classList.toggle('viewport-mobile', mode === 'mobile');
                frameCard.classList.toggle('viewport-desktop', mode !== 'mobile');
            }
            window.setTimeout(updateOverlay, 100);
        });
    });

    document.getElementById('btnResetPage').addEventListener('click', async () => {
        if (!confirm('Bu səhifənin bütün visual dəyişiklikləri silinsin/sıfırlansın?')) return;
        const fd = new FormData();
        fd.append('action', 'reset_page');
        fd.append('page_key', CONFIG.pageKey);
        fd.append('lang_code', CONFIG.langCode);
        const data = await postApi(fd);
        if (!data.ok) {
            setStatus('error', data.message || 'Sıfırlanmadı.');
            return;
        }
        pendingEdits.clear();
        pendingBlocks = [];
        undoStack = [];
        redoStack = [];
        setDirty();
        note('Səhifə sıfırlandı.');
        iframe.contentWindow.location.reload();
    });

    document.getElementById('btnSaveAll').addEventListener('click', async () => {
        if (!pendingEdits.size && !pendingBlocks.length) {
            setStatus('', 'Yadda saxlanacaq dəyişiklik yoxdur.');
            return;
        }
        const fd = new FormData();
        fd.append('action', 'save_batch');
        fd.append('page_key', CONFIG.pageKey);
        fd.append('lang_code', CONFIG.langCode);
        fd.append('edits', JSON.stringify(Array.from(pendingEdits.values())));
        fd.append('blocks', JSON.stringify(pendingBlocks));
        setStatus('dirty', 'Yadda saxlanılır...');
        const data = await postApi(fd);
        if (!data.ok) {
            setStatus('error', data.message || 'Yadda saxlanmadı.');
            return;
        }
        pendingEdits.clear();
        pendingBlocks = [];
        undoStack = [];
        redoStack = [];
        setDirty();
        note('Yadda saxlanıldı. Public sayta düşdü.');
    });

    document.getElementById('imageUpload').addEventListener('change', async function(){
        if (!selectedEl || !this.files || !this.files[0]) return alert('Şəkil üçün əvvəl obyekt seç.');
        const imgEl = selectedEl.tagName && selectedEl.tagName.toLowerCase() === 'img' ? selectedEl : selectedEl.querySelector('img');
        if (!imgEl) return alert('Bu obyektin içində şəkil tapılmadı. Birbaşa şəklin üstünə klik et.');
        pushUndo('Şəkil');
        const fd = new FormData();
        fd.append('action', 'upload_image');
        fd.append('page_key', CONFIG.pageKey);
        fd.append('lang_code', CONFIG.langCode);
        fd.append('image', this.files[0]);
        const data = await postApi(fd);
        if (!data.ok) return alert(data.message || 'Şəkil yüklənmədi.');
        selectedEl = imgEl;
        selectedSelector = getSelector(imgEl);
        queueEdit(imgEl, selectedSelector, 'src', data.url);
        selectElement(imgEl);
        note('Şəkil tətbiq olundu.');
    });

    document.querySelectorAll('[data-tool]').forEach(btn => {
        btn.addEventListener('click', () => addToolBlock(btn.getAttribute('data-tool')));
    });

    function addToolBlock(tool) {
        if (!frameDoc) return alert('Səhifə tam yüklənməyib.');
        pushUndo('Yeni blok');
        const html = buildToolHtml(tool || 'card');
        const target = selectedEl ? (selectedEl.parentElement || frameDoc.querySelector('main') || frameDoc.body) : (frameDoc.querySelector('main') || frameDoc.body);
        appendBlockLocally(html, getSelector(target));
    }

    function appendBlockLocally(html, targetSelector) {
        let target = null;
        try { target = targetSelector ? frameDoc.querySelector(targetSelector) : null; } catch(e) { target = null; }
        if (!target || isProtectedElement(target)) {
            target = frameDoc.querySelector('main') || frameDoc.body;
            targetSelector = getSelector(target);
        }
        const wrap = frameDoc.createElement('div');
        wrap.innerHTML = html;
        const inserted = [];
        while (wrap.firstChild) {
            const node = wrap.firstChild;
            inserted.push(node);
            target.appendChild(node);
        }
        pendingBlocks.push({ target_selector: targetSelector || 'main', block_html: html, sort_order: Date.now() });
        setDirty();
        buildObjectTree();
        if (inserted[0] && inserted[0].nodeType === 1) selectElement(inserted[0]);
        note('Blok əlavə olundu.');
    }

    function uniqueVeId() {
        return 've_' + Date.now() + '_' + Math.random().toString(16).slice(2);
    }

    function buildToolHtml(type) {
        const id = uniqueVeId();
        if (type === 'heading') return `<section data-ve-id="${id}" class="ve-tool-box ve-added-block ve-anim-up"><h2 class="ve-tool-title">Yeni başlıq</h2></section>`;
        if (type === 'text') return `<section data-ve-id="${id}" class="ve-tool-box ve-added-block ve-anim-up"><p class="ve-tool-text">Yeni mətn bloku</p></section>`;
        if (type === 'image') return `<section data-ve-id="${id}" class="ve-tool-box ve-added-block ve-anim-up"><h2 class="ve-tool-title">Şəkil bloku</h2><p class="ve-tool-text">Şəkli seçib sağ paneldən dəyişə bilərsən.</p><img class="ve-tool-img" src="/uploads/site/default.jpg" alt="Şəkil"></section>`;
        if (type === 'button') return `<section data-ve-id="${id}" class="ve-tool-box ve-added-block ve-anim-up" style="text-align:center"><a class="ve-tool-btn" href="#">Düymə</a></section>`;
        if (type === 'alert') return `<section data-ve-id="${id}" class="ve-tool-box ve-added-block ve-anim-up" style="background:#fff4e8;border-color:#ffd0a3;color:#9a4b00"><h2 class="ve-tool-title">Bildiriş</h2><p class="ve-tool-text">Bildiriş mətni</p></section>`;
        if (type === 'group') return `<section data-ve-id="${id}" class="ve-tool-box ve-added-block ve-anim-up"><fieldset style="border:1px solid #dbe4ee;border-radius:18px;padding:18px"><legend style="font-weight:900">Qrup</legend><p class="ve-tool-text">Qrup məzmunu</p></fieldset></section>`;
        if (type === 'spacer') return `<div data-ve-id="${id}" class="ve-tool-spacer ve-added-block ve-anim-up"></div>`;
        if (type === 'divider') return `<div data-ve-id="${id}" class="ve-tool-divider ve-added-block ve-anim-up"></div>`;
        return `<section data-ve-id="${id}" class="ve-tool-box ve-added-block ve-anim-up"><h2 class="ve-tool-title">Yeni blok</h2><p class="ve-tool-text">Mətn daxil edin</p></section>`;
    }

    function applyLocal(el, type, value) {
        if (!el) return;
        if (type === 'text') el.textContent = value || '';
        if (type === 'html') el.innerHTML = value || '';
        if (type === 'href') el.setAttribute('href', value || '#');
        if (type === 'src') el.setAttribute('src', value || '');
        if (type === 'hide') el.style.display = 'none';
        if (type === 'style') {
            try {
                const st = JSON.parse(value || '{}');
                Object.keys(st).forEach(k => el.style[k] = st[k]);
            } catch(e) {}
        }
    }

    function applySavedBlocks() {
        CONFIG.blocks.forEach(block => {
            if (!block.block_html) return;
            let target = null;
            try { target = block.target_selector ? frameDoc.querySelector(block.target_selector) : null; } catch(e) { target = null; }
            if (!target || isProtectedElement(target)) target = frameDoc.querySelector('main') || frameDoc.body;
            const wrap = frameDoc.createElement('div');
            wrap.innerHTML = block.block_html;
            while (wrap.firstChild) target.appendChild(wrap.firstChild);
        });
    }

    function applySavedEdits() {
        CONFIG.edits.forEach(item => {
            if (!item.selector || !item.edit_type) return;
            let nodes = [];
            try { nodes = frameDoc.querySelectorAll(item.selector); } catch(e) { return; }
            nodes.forEach(el => {
                if (!isProtectedElement(el)) applyLocal(el, item.edit_type, item.edit_value || '');
            });
        });
    }

    function buildObjectTree() {
        if (!frameDoc || !objectTree) return;
        const openSelectors = new Set(Array.from(document.querySelectorAll('.ve-tree-node.is-open>.ve-tree-row')).map(row => row.dataset.selector).filter(Boolean));
        treeNodeMap = new Map();
        objectTree.innerHTML = '';
        const root = frameDoc.querySelector('main') || frameDoc.body;
        let nodeIndex = 0;
        const buildNode = (el, level) => {
            if (!el || nodeIndex >= 240 || isProtectedElement(el) || !isRenderable(el)) return null;
            const selector = getSelector(el);
            const key = 'node_' + nodeIndex++;
            treeNodeMap.set(key, el);

            const childNodes = Array.from(el.children || [])
                .filter(child => !isProtectedElement(child) && isRenderable(child))
                .map(child => buildNode(child, level + 1))
                .filter(Boolean);

            const node = document.createElement('div');
            node.className = 've-tree-node';
            node.dataset.selector = selector;

            const row = document.createElement('div');
            row.className = 've-tree-row';
            row.dataset.nodeKey = key;
            row.dataset.selector = selector;
            row.setAttribute('role', 'treeitem');
            if (childNodes.length) row.setAttribute('aria-expanded', 'false');

            const disclosure = document.createElement('button');
            disclosure.type = 'button';
            disclosure.className = 've-tree-disclosure' + (childNodes.length ? '' : ' is-empty');
            disclosure.innerHTML = '<i class="ti ti-chevron-right"></i>';
            disclosure.setAttribute('aria-label', childNodes.length ? 'Alt obyektləri aç' : 'Alt obyekt yoxdur');
            if (!childNodes.length) {
                disclosure.tabIndex = -1;
            } else {
                disclosure.addEventListener('click', e => {
                    e.preventDefault();
                    e.stopPropagation();
                    const isOpen = node.classList.toggle('is-open');
                    row.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            const icon = document.createElement('i');
            icon.className = treeIcon(el);

            const label = document.createElement('button');
            label.type = 'button';
            label.className = 've-tree-select';
            label.innerHTML = '<span>' + escapeHtml(elementLabel(el)) + '<small>' + escapeHtml(textPreview(el)) + '</small></span>';
            label.addEventListener('click', e => {
                e.preventDefault();
                e.stopPropagation();
                const target = treeNodeMap.get(key);
                if (target) selectElement(target);
            });

            row.addEventListener('click', e => {
                if (e.target.closest('.ve-tree-disclosure')) return;
                const target = treeNodeMap.get(key);
                if (target) selectElement(target);
            });

            row.appendChild(disclosure);
            row.appendChild(icon);
            row.appendChild(label);
            node.appendChild(row);

            if (childNodes.length) {
                const childrenWrap = document.createElement('div');
                childrenWrap.className = 've-tree-children';
                childNodes.forEach(child => childrenWrap.appendChild(child));
                node.appendChild(childrenWrap);
                if (openSelectors.has(selector)) {
                    node.classList.add('is-open');
                    row.setAttribute('aria-expanded', 'true');
                }
            }

            return node;
        };
        const rootNodes = Array.from(root.children || [])
            .filter(child => !isProtectedElement(child) && isRenderable(child))
            .map(child => buildNode(child, 0))
            .filter(Boolean);
        if (!rootNodes.length) {
            objectTree.innerHTML = '<div class="ve-tree-empty">Bu səhifədə seçilə bilən obyekt tapılmadı.</div>';
            return;
        }
        rootNodes.forEach(node => objectTree.appendChild(node));
        markTreeActive();
    }

    function isRenderable(el) {
        if (!el || !el.tagName) return false;
        const tag = el.tagName.toLowerCase();
        if (['script','style','meta','link','title','br'].includes(tag)) return false;
        const rect = el.getBoundingClientRect();
        return rect.width > 0 || rect.height > 0 || (el.textContent || '').trim() !== '';
    }

    function textPreview(el) {
        const txt = (el.innerText || el.textContent || '').replace(/\s+/g,' ').trim();
        return txt ? txt.slice(0, 54) : getSelector(el).slice(0, 54);
    }

    function treeIcon(el) {
        const tag = el.tagName ? el.tagName.toLowerCase() : '';
        if (tag === 'img' || el.querySelector('img')) return 'ti ti-photo';
        if (tag === 'a' || tag === 'button') return 'ti ti-click';
        if (/^h[1-6]$/.test(tag)) return 'ti ti-heading';
        if (['section','main','article','aside','header','footer','nav'].includes(tag)) return 'ti ti-layout';
        if (['ul','ol','li'].includes(tag)) return 'ti ti-list';
        return 'ti ti-box';
    }

    function markTreeActive() {
        document.querySelectorAll('.ve-tree-row.active').forEach(row => row.classList.remove('active'));
        if (!selectedSelector) return;
        const row = Array.from(document.querySelectorAll('.ve-tree-row')).find(item => item.dataset.selector === selectedSelector);
        if (row) {
            row.classList.add('active');
            let parentNode = row.closest('.ve-tree-node');
            while (parentNode) {
                parentNode.classList.add('is-open');
                const parentRow = parentNode.querySelector(':scope>.ve-tree-row');
                if (parentRow && parentRow.getAttribute('aria-expanded') !== 'undefined') parentRow.setAttribute('aria-expanded', 'true');
                const parentChildren = parentNode.parentElement ? parentNode.parentElement.closest('.ve-tree-node') : null;
                parentNode = parentChildren;
            }
            row.scrollIntoView({ block: 'nearest' });
        }
    }

    async function postApi(fd) {
        if (CONFIG.csrf && !fd.has('_token')) fd.append('_token', CONFIG.csrf);
        const res = await fetch(CONFIG.apiUrl, { method:'POST', body:fd, credentials:'same-origin' });
        const text = await res.text();
        try { return JSON.parse(text); } catch(e) {
            return { ok:false, message:'Server JSON qaytarmadı. Cavab: ' + text.slice(0, 700) };
        }
    }

    function getSelector(el) {
        if (!el) return '';
        if (el.id) return '#' + cssEscape(el.id);
        if (el.getAttribute && el.getAttribute('data-ve-id')) return '[data-ve-id="' + cssEscape(el.getAttribute('data-ve-id')) + '"]';
        const parts = [];
        while (el && el.nodeType === 1 && el !== frameDoc.documentElement) {
            let part = el.tagName.toLowerCase();
            if (el.className && typeof el.className === 'string') {
                const cls = el.className.split(/\s+/).filter(c => c && !c.startsWith('ve-')).slice(0, 2);
                if (cls.length) part += '.' + cls.map(cssEscape).join('.');
            }
            const parent = el.parentElement;
            if (parent) {
                const siblings = Array.from(parent.children).filter(s => s.tagName === el.tagName);
                if (siblings.length > 1) part += ':nth-of-type(' + (siblings.indexOf(el) + 1) + ')';
            }
            parts.unshift(part);
            const selector = parts.join(' > ');
            try {
                if (frameDoc.querySelectorAll(selector).length === 1 && selector.length <= 340) return selector;
            } catch(e) {}
            el = parent;
        }
        const finalSelector = parts.join(' > ');
        return finalSelector.length > 340 ? finalSelector.slice(0, 340) : finalSelector;
    }

    function cssEscape(str) {
        return String(str).replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');
    }

    function escapeHtml(text) {
        return String(text || '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]));
    }

    function note(text) {
        if (!frameDoc) return;
        const box = frameDoc.getElementById('veNote');
        if (!box) return;
        box.textContent = text;
        box.style.display = 'block';
        clearTimeout(box._veTimer);
        box._veTimer = setTimeout(() => box.style.display = 'none', 2200);
    }

    window.addEventListener('beforeunload', function(e){
        if (pendingEdits.size || pendingBlocks.length) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    updateHistoryButtons();
})();
</script>
</body>
</html>
