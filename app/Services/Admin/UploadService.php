<?php

namespace App\Services\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Throwable;

class UploadService
{
    public function store(?UploadedFile $file, string $folder): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        if ($file->getSize() === false || $file->getSize() > 10 * 1024 * 1024) {
            return null;
        }

        $allowed = [
            'jpg' => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'gif' => ['image/gif'],
            // SVG is parsed and normalized below. Some Windows/PHP setups detect a valid SVG as octet-stream.
            'svg' => ['image/svg+xml', 'text/plain', 'text/xml', 'application/octet-stream'],
            'pdf' => ['application/pdf'],
        ];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! array_key_exists($extension, $allowed)) {
            return null;
        }

        $mime = strtolower((string) ($file->getMimeType() ?: ''));
        if (! in_array($mime, $allowed[$extension], true)) {
            return null;
        }

        $normalizedSvg = null;
        if ($extension === 'svg') {
            $rawSvg = @file_get_contents($file->getRealPath());
            $normalizedSvg = is_string($rawSvg) ? $this->normalizeSvg($rawSvg) : null;
            if ($normalizedSvg === null) {
                return null;
            }
        }

        $directory = public_path('uploads/'.trim($folder, '/'));

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return null;
        }

        if (! is_writable($directory)) {
            return null;
        }

        $filename = now()->format('YmdHis').'_'.Str::random(12).'.'.$extension;
        $target = $directory.DIRECTORY_SEPARATOR.$filename;

        try {
            if ($normalizedSvg !== null) {
                if (file_put_contents($target, $normalizedSvg, LOCK_EX) === false) {
                    return null;
                }
            } else {
                $file->move($directory, $filename);
            }
        } catch (Throwable) {
            return null;
        }

        return 'uploads/'.trim($folder, '/').'/'.$filename;
    }

    public function safeSvg(string $svg): bool
    {
        return $this->normalizeSvg($svg) !== null;
    }

    /**
     * Removes legacy external SVG DTD declarations, then rejects active or unsafe SVG constructs.
     * A normalized copy is stored, so the server never keeps the external DOCTYPE.
     */
    private function normalizeSvg(string $svg): ?string
    {
        if ($svg === '' || strlen($svg) > 2 * 1024 * 1024) {
            return null;
        }

        $svg = preg_replace('/^\xEF\xBB\xBF/', '', $svg) ?? $svg;
        // Common legacy exports include a public SVG 1.0 DTD. It is not needed by browsers and is removed safely.
        $svg = preg_replace('/<!DOCTYPE(?:[^>\[]|\[[\s\S]*?\])*?>/i', '', $svg);
        if (! is_string($svg) || trim($svg) === '') {
            return null;
        }

        if (preg_match('/<!ENTITY|<script\b|<foreignObject\b|\son[a-z]+\s*=|javascript:|data\s*:\s*text\/html|@import\s+/i', $svg)) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($svg, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $document !== false && strtolower($document->getName()) === 'svg' ? $svg : null;
    }
}
