<?php

namespace App\PageBuilder\Services;

use App\PageBuilder\Models\Page;
use App\PageBuilder\Support\TemplateCatalog;
use Illuminate\Validation\ValidationException;

final class PageMetadataWriter
{
    public function __construct(private readonly TemplateCatalog $templates)
    {
    }

    /** @param array<string, mixed> $meta */
    public function fill(Page $page, array $meta, string $fallbackTitle): Page
    {
        $page->title = $this->requiredTitle($meta['title'] ?? $page->title ?? $fallbackTitle);
        $page->meta_title = $this->nullableText($meta['meta_title'] ?? $page->meta_title, 255);
        $page->meta_description = $this->nullableText($meta['meta_description'] ?? $page->meta_description, 500);
        $page->meta_keywords = $this->nullableText($meta['meta_keywords'] ?? $page->meta_keywords, 255);
        $page->template = $this->template($meta['template'] ?? $page->template);
        $page->is_active = true;

        return $page;
    }

    private function requiredTitle(mixed $value): string
    {
        $text = trim((string) $value);
        if ($text === '' || mb_strlen($text) > 255) {
            throw ValidationException::withMessages([
                'meta.title' => ['A page title between 1 and 255 characters is required.'],
            ]);
        }
        return $text;
    }

    private function nullableText(mixed $value, int $limit): ?string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }
        if (mb_strlen($text) > $limit) {
            throw ValidationException::withMessages([
                'meta' => ['A metadata field is too long.'],
            ]);
        }
        return $text;
    }

    private function template(mixed $value): ?string
    {
        $template = trim((string) ($value ?? ''));
        if ($template === '') {
            return null;
        }
        if (! array_key_exists($template, $this->templates->all())) {
            throw ValidationException::withMessages([
                'meta.template' => ['Unknown page template.'],
            ]);
        }
        return $template;
    }
}

