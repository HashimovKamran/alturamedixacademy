@php
    /** @var \Illuminate\Http\Request $request */
    $request = request();
    $visualService = app(\App\Services\Site\VisualEditorService::class);
    $visualPageKey = $visualService->pageKeyFromRequest($request);
    $visualLang = $lang ?? (string) $request->query('lang', 'az');
    $visualEdits = $visualService->isPreview($request) ? collect() : $visualService->edits($visualLang, $visualPageKey);
    $visualBlocks = $visualService->isPreview($request) ? collect() : $visualService->blocks($visualLang, $visualPageKey);

    $visualEditsPayload = $visualEdits->map(function ($item) {
        return [
            'selector' => $item->selector,
            'edit_type' => $item->edit_type,
            'edit_value' => $item->edit_value,
        ];
    })->values();

    $visualBlocksPayload = $visualBlocks->map(function ($item) {
        return [
            'target_selector' => $item->target_selector,
            'block_html' => $item->block_html,
            'sort_order' => $item->sort_order,
        ];
    })->values();
@endphp

@if($visualEdits->isNotEmpty() || $visualBlocks->isNotEmpty())
<script id="vpub-visual-editor-script">
(function(){
    if(window.__VPUB_VISUAL_EDITOR_APPLIED__) return;
    window.__VPUB_VISUAL_EDITOR_APPLIED__ = true;
    const VE_EDITS = {!! \Illuminate\Support\Js::from($visualEditsPayload) !!};
    const VE_BLOCKS = {!! \Illuminate\Support\Js::from($visualBlocksPayload) !!};
    const protectedSelectorWords = ['header','site-header','header-top','header-tools','header-nav','main-nav','nav-inner','nav-actions','brand','brand-logo','brand-text','language','language-dropdown','language-current','language-menu','social','social-links','footer','site-footer','footer-brand','footer-social','mobile-menu','search-btn','auth-backdrop','site-search'];
    function ready(fn){document.readyState==='loading'?document.addEventListener('DOMContentLoaded',fn):fn();}
    function selectorIsProtected(selector){selector=String(selector||'').toLowerCase();return protectedSelectorWords.some(word=>selector.includes(word));}
    function isProtectedElement(el){return !el || !el.closest || !!el.closest('header,.site-header,.header-top,.header-tools,.header-nav,.main-nav,.nav-inner,.nav-actions,.brand,.brand-logo,.brand-text,.language-dropdown,.language-current,.language-menu,.social-links,footer,.site-footer,.footer-brand,.footer-social,.auth-backdrop,.site-search-inline');}
    function findTarget(selector){let target=null;if(selector&&!selectorIsProtected(selector)){try{target=document.querySelector(selector)}catch(e){target=null}} if(target&&!isProtectedElement(target)) return target; return document.querySelector('#ve-page-root')||document.querySelector('[data-ve-root]')||document.querySelector('main')||document.querySelector('.main-content')||document.querySelector('.page-content')||document.querySelector('.site-main')||document.querySelector('.content')||document.body;}
    function applyLocal(el,type,value){if(!el||isProtectedElement(el)) return; if(type==='text'){el.textContent=value||'';return} if(type==='html'){el.innerHTML=value||'';return} if(type==='href'){el.setAttribute('href',value||'#');return} if(type==='src'){el.setAttribute('src',value||'');return} if(type==='hide'){el.style.display='none';return} if(type==='style'){try{const st=JSON.parse(value||'{}');Object.keys(st).forEach(k=>el.style[k]=st[k]);}catch(e){}}}
    function applyBlocks(){VE_BLOCKS.forEach(block=>{if(!block.block_html||selectorIsProtected(block.target_selector||'')) return; const target=findTarget(block.target_selector||''); if(isProtectedElement(target)) return; const wrap=document.createElement('div'); wrap.innerHTML=block.block_html; while(wrap.firstChild) target.appendChild(wrap.firstChild);});}
    function applyEdits(){VE_EDITS.forEach(item=>{if(!item.selector||!item.edit_type||selectorIsProtected(item.selector)) return; let nodes=[]; try{nodes=document.querySelectorAll(item.selector)}catch(e){return} nodes.forEach(el=>applyLocal(el,item.edit_type,item.edit_value||''));});}
    ready(function(){applyBlocks();applyEdits();setTimeout(applyEdits,150);setTimeout(applyEdits,500);setTimeout(applyEdits,1200);});
})();
</script>
@endif
