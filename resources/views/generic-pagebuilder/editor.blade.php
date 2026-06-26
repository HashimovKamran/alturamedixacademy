<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page Builder</title>
    @vite([
        'resources/css/pagebuilder.css',
        'resources/css/pagebuilder-v2.css',
        'resources/js/pagebuilder/editor-runtime.tsx',
    ])
</head>
<body class="pb-app-body">
    <div
        id="page-builder-root"
        data-slug="{{ $slug }}"
        data-api-base="{{ url('/'.trim((string) config('page_builder.prefix', 'pagebuilder'), '/').'/api') }}"
        data-editor-base="{{ url('/'.trim((string) config('page_builder.prefix', 'pagebuilder'), '/').'/editor') }}"
        data-canvas-base="{{ url('/'.trim((string) config('page_builder.prefix', 'pagebuilder'), '/').'/canvas') }}"
        data-canvas-mode="public"
        data-preview-base="{{ url('/'.trim((string) config('page_builder.prefix', 'pagebuilder'), '/').'/preview') }}"
        data-public-origin="{{ url('/') }}"
    ></div>
</body>
</html>

