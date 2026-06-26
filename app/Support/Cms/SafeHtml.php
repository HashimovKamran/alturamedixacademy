<?php

namespace App\Support\Cms;

use DOMDocument;
use DOMElement;
use DOMNode;

class SafeHtml
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li',
        'a', 'span', 'h2', 'h3', 'h4', 'blockquote', 'code', 'pre',
    ];

    public function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        if ($html === strip_tags($html)) {
            return nl2br(e($html));
        }

        if (! class_exists(DOMDocument::class)) {
            return $this->fallback($html);
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="cms-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return $this->fallback($html);
        }

        $root = $dom->getElementById('cms-root');
        if (! $root) {
            return $this->fallback($html);
        }

        $this->sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                    $parent->removeChild($node);

                    continue;
                }

                $this->sanitizeChildren($node);
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }

            foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
                $name = strtolower($attribute->name);
                if ($tag !== 'a' || ! in_array($name, ['href', 'title', 'target', 'rel'], true)) {
                    $node->removeAttribute($attribute->name);
                }
            }

            if ($tag === 'a') {
                $node->setAttribute('href', SafeUrl::clean($node->getAttribute('href')));
                if ($node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                } else {
                    $node->removeAttribute('target');
                    $node->removeAttribute('rel');
                }
            }

            $this->sanitizeChildren($node);
        }
    }

    private function fallback(string $html): string
    {
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><s><ul><ol><li><a><span><h2><h3><h4><blockquote><code><pre>');
        $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?: '';
        $html = preg_replace('/\sstyle\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $html) ?: $html;

        return preg_replace_callback('/href\s*=\s*(["\'])(.*?)\1/i', static fn ($match) => 'href="'.e(SafeUrl::clean($match[2])).'"', $html) ?: '';
    }
}
