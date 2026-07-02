<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnableCategoryRasterImagePicker
{
    /**
     * The category visual picker is rendered by the shared module template.
     * This response-level enhancement keeps the reusable icon picker intact
     * while extending its image selector to all supported safe image formats.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->route('module') !== 'categories' || ! $this->isHtmlResponse($response)) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || ! str_contains($content, 'name="image_path"')) {
            return $response;
        }

        $script = <<<'HTML'
<script>
(() => {
    const input = document.querySelector('input[name="image_path"][data-module-file-input]');
    if (!input) return;

    input.setAttribute('accept', '.svg,.png,.jpg,.jpeg,.webp,image/svg+xml,image/png,image/jpeg,image/webp');

    const upload = input.closest('.module-file-upload');
    if (upload) {
        const title = upload.querySelector('.module-file-copy strong');
        const note = upload.querySelector('[data-module-file-name]');
        const icon = upload.querySelector('.module-file-icon i');
        if (title) title.textContent = 'Şəkil seç';
        if (note) note.textContent = 'SVG, PNG, JPG/JPEG və WEBP faylı yüklə';
        if (icon) icon.className = 'ti ti-photo-up';
    }

    const visualChoice = document.querySelector('[data-category-visual] .target-option:nth-child(2)');
    if (visualChoice) {
        const title = visualChoice.querySelector('strong');
        const note = visualChoice.querySelector('small');
        const icon = visualChoice.querySelector('i');
        if (title) title.textContent = 'Şəkil';
        if (note) note.textContent = 'SVG, PNG, JPG/JPEG və WEBP faylı yüklə.';
        if (icon) icon.className = 'ti ti-photo';
    }
})();
</script>
HTML;

        $response->setContent(str_ireplace('</body>', $script."\n</body>", $content));

        return $response;
    }

    private function isHtmlResponse(Response $response): bool
    {
        $contentType = (string) $response->headers->get('Content-Type', '');

        return $contentType === '' || str_contains(strtolower($contentType), 'text/html');
    }
}
