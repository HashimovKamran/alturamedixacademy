@php
    /** @var \Illuminate\Http\Request $request */
    $request = request();
    $visualService = app(\App\Services\Site\VisualEditorService::class);
    $visualPageKey = $visualService->pageKeyFromRequest($request);
    $visualLang = $lang ?? (string) $request->query('lang', 'az');
    $visualEdits = $visualService->isPreview($request) ? collect() : $visualService->edits($visualLang, $visualPageKey);
    $visualBlocks = $visualService->isPreview($request) ? collect() : $visualService->blocks($visualLang, $visualPageKey);
    $allowedStyleKeys = [
        'color' => 'color',
        'backgroundColor' => 'background-color',
        'fontSize' => 'font-size',
        'borderRadius' => 'border-radius',
        'padding' => 'padding',
        'boxShadow' => 'box-shadow',
        'margin' => 'margin',
        'fontWeight' => 'font-weight',
        'textAlign' => 'text-align',
        'lineHeight' => 'line-height',
        'letterSpacing' => 'letter-spacing',
        'width' => 'width',
        'height' => 'height',
        'minHeight' => 'min-height',
        'maxWidth' => 'max-width',
        'opacity' => 'opacity',
        'position' => 'position',
        'left' => 'left',
        'top' => 'top',
        'right' => 'right',
        'bottom' => 'bottom',
        'zIndex' => 'z-index',
        'transform' => 'transform',
    ];
    $safeSelector = static function (?string $selector): string {
        $selector = trim((string) $selector);
        if ($selector === '' || str_contains($selector, '{') || str_contains($selector, '}') || str_contains($selector, ';')) {
            return '';
        }
        return preg_match('/^[a-zA-Z0-9\s\.\#\>\+\~\:\(\)\[\]\=\"\'_\-]+$/', $selector) ? $selector : '';
    };
    $safeValue = static function (?string $value): string {
        $value = trim((string) $value);
        $value = str_replace(['</style', '<style', '{', '}', ';'], '', $value);
        return preg_replace('/\/\*|\*\//', '', $value) ?: '';
    };
    $visualCssRules = [];

    foreach ($visualEdits as $edit) {
        $selector = $safeSelector($edit->selector);
        if ($selector === '') {
            continue;
        }

        if ($edit->edit_type === 'hide') {
            $visualCssRules[] = $selector . '{display:none!important;}';
            continue;
        }

        if ($edit->edit_type !== 'style') {
            continue;
        }

        $styles = json_decode((string) $edit->edit_value, true);
        if (! is_array($styles)) {
            continue;
        }

        $declarations = [];
        foreach ($styles as $key => $value) {
            if (! array_key_exists((string) $key, $allowedStyleKeys)) {
                continue;
            }
            $value = $safeValue((string) $value);
            if ($value !== '') {
                $declarations[] = $allowedStyleKeys[(string) $key] . ':' . $value . '!important';
            }
        }

        if ($declarations !== []) {
            $visualCssRules[] = $selector . '{' . implode(';', $declarations) . ';}';
        }
    }
@endphp

@if($visualEdits->isNotEmpty() || $visualBlocks->isNotEmpty())
<style id="vpub-visual-editor-style">
.ve-added-block{animation:.45s ease both}.ve-anim-fade{animation-name:veFade}.ve-anim-up{animation-name:veUp}.ve-anim-zoom{animation-name:veZoom}@keyframes veFade{from{opacity:0}to{opacity:1}}@keyframes veUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}@keyframes veZoom{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}.ve-added-block.ve-card,.ve-tool-box{margin:24px auto;max-width:1180px;background:#fff;border:1px solid #dbe4ee;border-radius:22px;padding:28px;box-shadow:0 18px 55px rgba(7,23,40,.07);color:#071728}.ve-tool-title{margin:0 0 10px;font-size:28px;line-height:1.2;font-weight:900;color:#071728}.ve-tool-text{margin:0;font-size:16px;line-height:1.75;color:#334155;font-weight:650}.ve-tool-btn{margin-top:16px;display:inline-flex;align-items:center;gap:8px;background:#ff8a1c;color:#fff;padding:11px 16px;border-radius:12px;text-decoration:none;font-weight:900}.ve-tool-img{width:100%;max-height:360px;object-fit:cover;border-radius:18px;margin-top:16px}.ve-tool-video{width:100%;max-height:420px;border-radius:18px;margin-top:16px;background:#000}.ve-tool-form input,.ve-tool-form textarea,.ve-tool-form select{width:100%;border:1px solid #dbe4ee;border-radius:12px;padding:12px 13px;margin-top:8px;font-family:inherit}.ve-tool-form label{display:block;margin:12px 0 4px;font-weight:900}.ve-tool-alert{background:#fff4e8;border:1px solid #ffd0a3;color:#9a4b00}.ve-tool-divider{height:1px;background:#dbe4ee;margin:28px auto;max-width:1180px}.ve-tool-spacer{height:50px}.ve-group-box{border:2px dashed rgba(255,138,28,.75);border-radius:18px;padding:14px;margin:12px 0;background:rgba(255,138,28,.04)}@media(max-width:700px){.ve-added-block.ve-card,.ve-tool-box{margin:16px 12px;padding:20px;border-radius:18px}.ve-tool-title{font-size:22px}}
@foreach($visualCssRules as $rule)
{!! $rule !!}
@endforeach
</style>
@endif
