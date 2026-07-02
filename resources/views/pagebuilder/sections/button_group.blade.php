@pbSchema(['name' => 'button_group.blade'])
<div class="pb-button-group">
@if($children->isNotEmpty())
@foreach($children as $child)
@php($button = $child['block'])
<a class="pb-btn {{ ($button->style ?? '') === 'light' ? 'is-light' : '' }}" href="{{ \App\Support\Cms\SafeUrl::clean($button->url ?? '#') }}">{{ $button->title ?? '' }}</a>
@endforeach
@else
@foreach((array)($content['items'] ?? []) as $item)
<a class="pb-btn {{ ($item['style'] ?? '') === 'light' ? 'is-light' : '' }}" href="{{ \App\Support\Cms\SafeUrl::clean($item['url'] ?? '#') }}">{{ $item['title'] ?? '' }}</a>
@endforeach
@endif
</div>
