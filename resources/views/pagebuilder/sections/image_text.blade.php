@pbSchema(['name' => 'image_text.blade'])
@php
    $imagePath = trim((string) ($block->image_path ?? ''));
    $imageUrl = preg_match('#^https?://#i', $imagePath) ? $imagePath : asset(ltrim($imagePath, '/'));
@endphp
<div class="pb-split {{ ($settings['image_position'] ?? 'right') === 'left' ? 'image-left' : 'image-right' }}">
    @if($imagePath)
        <div class="pb-media"><img src="{{ $imageUrl }}" alt="{{ $content['title'] ?? '' }}"></div>
    @endif
    <div class="pb-content">
        @if($content['eyebrow'] ?? false)<div class="pb-kicker">{{ $content['eyebrow'] }}</div>@endif
        @if($content['title'] ?? false)<h2>{{ $content['title'] }}</h2>@endif
        <div class="pb-body">{!! app(\App\Support\Cms\SafeHtml::class)->clean($content['html'] ?? '') !!}</div>
        @if($content['button_text'] ?? false)<a class="pb-btn" href="{{ \App\Support\Cms\SafeUrl::clean($content['button_url'] ?? '#') }}">{{ $content['button_text'] }}</a>@endif
    </div>
</div>
