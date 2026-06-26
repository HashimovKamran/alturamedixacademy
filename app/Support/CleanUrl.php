<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CleanUrl
{
    public static function to(string $path, ?string $language = null): string
    {
        $path = trim($path);

        if ($path === '' || $path === '#') {
            return '#';
        }

        if (Str::startsWith($path, ['#', 'http://', 'https://', '//', 'mailto:', 'tel:'])) {
            return $path;
        }

        [$cleanPath, $query] = self::normalizePathAndQuery($path);

        if ($language !== null && $language !== '') {
            if ($cleanPath === '/article' && !empty($query['slug'])) {
                return url('/' . rawurlencode($language) . '/articles/' . rawurlencode((string) $query['slug']));
            }
            if ($cleanPath === '/page' && !empty($query['key'])) {
                return url('/' . rawurlencode($language) . '/pages/' . rawurlencode((string) $query['key']));
            }
            if (in_array($cleanPath, ['/', '/about', '/contact', '/certificates', '/gallery', '/trainings', '/articles'], true)) {
                $localized = '/' . rawurlencode($language) . ($cleanPath === '/' ? '' : $cleanPath);
                return url($localized) . (count($query) ? '?' . http_build_query($query) : '');
            }
            $query['lang'] = $language;
        }

        return url($cleanPath) . (count($query) ? '?' . http_build_query($query) : '');
    }

    public static function redirectPath(Request $request): ?string
    {
        $path = '/' . ltrim($request->path(), '/');

        if (! Str::contains($path, '.php')) {
            return null;
        }

        [$cleanPath, $query] = self::normalizePathAndQuery($path);
        $query = array_merge($request->query(), $query);

        return url($cleanPath) . (count($query) ? '?' . http_build_query($query) : '');
    }

    public static function activeKey(string $path): string
    {
        [$cleanPath, $query] = self::normalizePathAndQuery($path);

        if (isset($query['key']) && trim((string) $query['key']) !== '') {
            return self::cleanKey((string) $query['key']);
        }

        $key = trim($cleanPath, '/');
        return $key !== '' ? self::cleanKey($key) : 'index';
    }

    public static function cleanKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9_\-]/i', '', $key) ?: '';

        return $key !== '' ? $key : 'index';
    }

    public static function normalizePathAndQuery(string $path): array
    {
        $path = trim($path);
        $parts = parse_url($path) ?: [];
        $rawPath = (string) ($parts['path'] ?? $path);
        $query = [];

        if (! empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        }

        $rawPath = '/' . trim($rawPath, '/');
        $map = [
            '/' => '/',
            '/index.php' => '/',
            '/about.php' => '/about',
            '/about' => '/about',
            '/page.php' => '/page',
            '/page' => '/page',
            '/contact.php' => '/contact',
            '/contact' => '/contact',
            '/certificates.php' => '/certificates',
            '/certificates' => '/certificates',
            '/gallery.php' => '/gallery',
            '/gallery' => '/gallery',
            '/trainings.php' => '/trainings',
            '/trainings' => '/trainings',
            '/articles.php' => '/articles',
            '/articles' => '/articles',
            '/article.php' => '/article',
            '/article' => '/article',
            '/profile.php' => '/profile',
            '/profile' => '/profile',
            '/search_api.php' => '/search',
            '/search' => '/search',
            '/login.php' => '/login',
            '/login' => '/login',
            '/register.php' => '/register',
            '/register' => '/register',
            '/logout.php' => '/logout',
            '/logout' => '/logout',
            '/google-login.php' => '/auth/google',
            '/google-callback.php' => '/auth/google/callback',
            '/google-token-login.php' => '/auth/google/token',
        ];

        if (isset($map[$rawPath])) {
            return [$map[$rawPath], $query];
        }

        if (Str::startsWith($rawPath, '/admin/')) {
            $adminMap = [
                '/admin/index.php' => '/admin',
                '/admin/dashboard.php' => '/admin/dashboard',
                '/admin/login.php' => '/admin/login',
                '/admin/logout.php' => '/admin/logout',
                '/admin/settings.php' => '/admin/settings',
                '/admin/users.php' => '/admin/users',
                '/admin/contact_messages.php' => '/admin/contact-messages',
                '/admin/logs.php' => '/admin/logs',
                '/admin/media.php' => '/admin/media',
                '/admin/pageedit.php' => '/admin/page-editor',
                '/admin/live_editor.php' => '/admin/live-editor',
                '/admin/visual_api.php' => '/admin/visual-api',
            ];

            if (isset($adminMap[$rawPath])) {
                return [$adminMap[$rawPath], $query];
            }

            if (preg_match('#^/admin/([a-z0-9_\-]+)\.php$#i', $rawPath, $m)) {
                return ['/admin/' . str_replace('_', '-', $m[1]), $query];
            }
        }

        if (preg_match('#^/(.+)\.php$#i', $rawPath, $m)) {
            return ['/' . str_replace('_', '-', $m[1]), $query];
        }

        return [$rawPath === '/' ? '/' : '/' . trim($rawPath, '/'), $query];
    }
}
