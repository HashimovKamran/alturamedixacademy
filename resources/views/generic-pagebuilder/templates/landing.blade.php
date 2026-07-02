<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->meta_title ?: $page->title }}</title>
    @if ($page->meta_description)<meta name="description" content="{{ $page->meta_description }}">@endif
    @vite('resources/css/pagebuilder.css')
</head>
<body class="pb-public" style="background:linear-gradient(180deg,#fff 0%,#f5f7ff 100%)">
    <main class="pb-public-main">
        @include('generic-pagebuilder.partials.public-document')
    </main>
</body>
</html>

