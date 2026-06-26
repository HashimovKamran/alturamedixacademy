<?php

use App\Http\Middleware\EnsureAdminAuthenticated;

return [
    'prefix' => env('PAGE_BUILDER_PREFIX', 'pagebuilder'),
    'public_prefix' => env('PAGE_BUILDER_PUBLIC_PREFIX', 'pagebuilder-site'),
    'disk' => env('PAGE_BUILDER_DISK', 'public'),
    'asset_directory' => env('PAGE_BUILDER_ASSET_DIRECTORY', 'pagebuilder'),

    // routes/web.php already supplies Laravel's web middleware group.
    // In production, builder and API routes require an authenticated user by default.
    // Replace or extend this list with your own authorization middleware for roles/permissions.
    'middleware' => [EnsureAdminAuthenticated::class, 'admin.role:super_admin,designer,editor,publisher'],

    'reserved_slugs' => ['admin', 'api', 'storage', 'build', 'vendor', 'login', 'register', 'pagebuilder'],
    'limits' => [
        'max_document_bytes' => 1_000_000,
        'max_sections' => 80,
        'max_blocks_per_parent' => 80,
        'max_tree_depth' => 8,
        'max_upload_bytes' => 10 * 1024 * 1024,
        'max_image_width' => 8_000,
        'max_image_height' => 8_000,
        'max_image_pixels' => 40_000_000,
    ],
    'assets' => [
        'extensions' => ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'],
        'mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'],
    ],
    'layout' => ['types' => ['default'], 'zones' => ['header', 'footer']],
    'sections' => [],
    'blocks' => [],
    'theme_settings' => [],
];

