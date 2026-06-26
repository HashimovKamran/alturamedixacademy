@pbSchema(['name' => 'faq.blade'])
@if($content['title'] ?? false)<h2>{{ $content['title'] }}</h2>@endif
<div class="pb-faq">
@if($children->isNotEmpty())
@foreach($children as $child)
<details><summary>{{ $child['block']->title ?? '' }}</summary><p>{{ $child['block']->text ?? '' }}</p></details>
@endforeach
@else
@foreach((array)($content['items'] ?? []) as $item)
<details><summary>{{ $item['title'] ?? '' }}</summary><p>{{ $item['text'] ?? '' }}</p></details>
@endforeach
@endif
</div>
