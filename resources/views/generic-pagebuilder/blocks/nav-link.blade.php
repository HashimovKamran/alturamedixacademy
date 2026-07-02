@php($settings = $block['settings'] ?? [])
<a href="{{ $settings['url'] ?? '#' }}" style="font-size:14px;font-weight:650;text-decoration:none;opacity:.75">{{ $settings['label'] ?? '' }}</a>

