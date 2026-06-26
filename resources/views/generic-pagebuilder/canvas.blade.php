<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/pagebuilder.css', 'resources/js/pagebuilder/canvas.tsx'])
</head>
<body class="pb-canvas-body">
    <div
        id="page-builder-canvas"
        data-slug="{{ $slug }}"
        data-api-base="{{ url('/'.trim((string) config('page_builder.prefix', 'pagebuilder'), '/').'/api') }}"
    ></div>
</body>
</html>

