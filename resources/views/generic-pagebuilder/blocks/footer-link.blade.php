@php($settings = $block['settings'] ?? [])
<a href="{{ $settings['url'] ?? '#' }}" style="font-size:13px;opacity:.76;text-decoration:none">{{ $settings['label'] ?? '' }}</a>

