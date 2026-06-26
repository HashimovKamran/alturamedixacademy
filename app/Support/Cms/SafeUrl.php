<?php

namespace App\Support\Cms;

class SafeUrl
{
    public static function clean(?string $url, string $fallback = '#'): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return $fallback;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return $url;
        }

        if (preg_match('#^(?:https?://|mailto:|tel:)#i', $url) === 1) {
            return $url;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) === 1) {
            return $fallback;
        }

        return preg_match('#^[a-z0-9][a-z0-9_./?&=%+#-]*$#i', $url) === 1 ? $url : $fallback;
    }
}
