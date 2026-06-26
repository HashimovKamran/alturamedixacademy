<?php

namespace App\Services\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

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
            'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
            'webp' => ['image/webp'], 'gif' => ['image/gif'], 'svg' => ['image/svg+xml', 'text/plain', 'text/xml'],
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

        if ($extension === 'svg' && ! $this->safeSvg((string) file_get_contents($file->getRealPath()))) {
            return null;
        }

        $directory = public_path('uploads/'.trim($folder, '/'));

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = now()->format('YmdHis').'_'.Str::random(12).'.'.$extension;
        $file->move($directory, $filename);

        return 'uploads/'.trim($folder, '/').'/'.$filename;
    }

    public function safeSvg(string $svg): bool
    {
        if ($svg === '' || strlen($svg) > 2 * 1024 * 1024) {
            return false;
        }

        if (preg_match('/<!DOCTYPE|<!ENTITY|<script|<foreignObject|\son[a-z]+\s*=|javascript:|data\s*:\s*text\/html/i', $svg)) {
            return false;
        }

        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($svg, 'SimpleXMLElement', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $document !== false && strtolower($document->getName()) === 'svg';
    }
}
