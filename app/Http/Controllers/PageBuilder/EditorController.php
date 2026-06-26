<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\PageBuilder\Support\SlugNormalizer;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class EditorController extends Controller
{
    public function __construct(private readonly SlugNormalizer $slugs)
    {
    }

    public function __invoke(?string $slug = null): View
    {
        try {
            $slug = $this->slugs->normalize($slug ?? 'home');
        } catch (ValidationException) {
            abort(404);
        }

        return view('generic-pagebuilder.editor', compact('slug'));
    }
}

