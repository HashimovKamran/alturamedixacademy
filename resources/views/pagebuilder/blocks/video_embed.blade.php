@pbSchema(['name' => 'video_embed.blade'])
@php
    $video = \App\Support\Cms\SafeUrl::clean($content['url'] ?? '');
    $host = strtolower((string) parse_url($video, PHP_URL_HOST));
    $allowed = str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be') || str_contains($host, 'vimeo.com');
@endphp
@if($content['title']??false)<h2>{{ $content['title'] }}</h2>@endif
@if($allowed)<div class="pb-video"><iframe src="{{ $video }}" title="{{ $content['title']??'Video' }}" loading="lazy" allowfullscreen></iframe></div>@endif
@if($content['caption']??false)<p>{{ $content['caption'] }}</p>@endif
