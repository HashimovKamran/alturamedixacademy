<?php

namespace App\PageBuilder\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

final class HtmlSanitizer
{
    private const ALLOWED_TAGS = ['a', 'b', 'blockquote', 'br', 'code', 'em', 'h2', 'h3', 'h4', 'li', 'ol', 'p', 'pre', 's', 'span', 'strong', 'sub', 'sup', 'u', 'ul'];
    private const FORBIDDEN_TAGS = ['applet', 'base', 'embed', 'form', 'iframe', 'math', 'object', 'script', 'style', 'svg', 'template'];

    public function sanitize(string $html): string
    {
        if ($html === '') {
            return '';
        }

        if (! class_exists(DOMDocument::class)) {
            return nl2br(htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        $oldErrors = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $document->loadHTML('<div id="page-builder-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT);
            $root = $document->getElementsByTagName('div')->item(0);

            if (! $root instanceof DOMElement) {
                return '';
            }

            $this->cleanChildren($root);

            return $this->innerHtml($root);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($oldErrors);
        }
    }

    private function cleanChildren(DOMNode $parent): void
    {
        $children = iterator_to_array($parent->childNodes);

        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::FORBIDDEN_TAGS, true)) {
                $parent->removeChild($child);
                continue;
            }

            $this->cleanChildren($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->unwrap($child);
                continue;
            }

            $this->cleanAttributes($child, $tag);
        }
    }

    private function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = $tag === 'a'
            ? ['href', 'target', 'rel', 'title']
            : ['class'];

        $attributes = [];
        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $name) {
            $normalized = strtolower($name);
            if (str_starts_with($normalized, 'on') || ! in_array($normalized, $allowed, true)) {
                $element->removeAttribute($name);
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $href = $this->normalizeUrl($element->getAttribute('href'));
            if (! $this->isSafeUrl($href, allowRelative: true)) {
                $element->removeAttribute('href');
            } else {
                $element->setAttribute('href', $href);
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    public function normalizeUrl(string $url): string
    {
        $decoded = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $withoutControls = preg_replace('/[\x00-\x20\x7F]+/u', '', $decoded) ?? '';

        return trim($withoutControls);
    }

    public function isSafeUrl(string $url, bool $allowRelative = true): bool
    {
        if ($url === '') {
            return false;
        }

        if ($allowRelative && (str_starts_with($url, '/') || str_starts_with($url, '#'))) {
            return ! str_starts_with($url, '//');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true);
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function innerHtml(DOMElement $element): string
    {
        $output = '';
        foreach ($element->childNodes as $child) {
            $output .= $element->ownerDocument?->saveHTML($child) ?? '';
        }

        return $output;
    }
}

