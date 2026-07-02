<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->meta_title ?: $page->title }}</title>
    @if ($page->meta_description)<meta name="description" content="{{ $page->meta_description }}">@endif
    @if ($page->meta_keywords)<meta name="keywords" content="{{ $page->meta_keywords }}">@endif
    @vite('resources/css/pagebuilder.css')
</head>
<body class="pb-public">
    <main class="pb-public-main">
        @include('generic-pagebuilder.partials.public-document')
    </main>
</body>
</html>

