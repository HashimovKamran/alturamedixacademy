<?php

namespace App\PageBuilder\Support;

use Illuminate\Validation\ValidationException;

final class SlugNormalizer
{
    public function normalize(string $slug): string
    {
        $value = trim(strtolower(trim($slug)), '/');

        if ($value === '') {
            return 'home';
        }

        if (in_array($value, config('page_builder.reserved_slugs', []), true)
            || preg_match('/^[a-z0-9]+(?:[a-z0-9-]*(?:\/[a-z0-9]+[a-z0-9-]*)*)?$/', $value) !== 1) {
            throw ValidationException::withMessages([
                'slug' => ['Invalid or reserved page slug.'],
            ]);
        }

        return $value;
    }
}

