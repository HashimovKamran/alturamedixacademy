@pbSchema(['name' => 'faq.blade'])
@if($content['title']??false)<h2>{{ $content['title'] }}</h2>@endif
<div class="pb-faq">@foreach($content['items']??[] as $item)<details><summary>{{ $item['title']??'' }}</summary><p>{{ $item['text']??'' }}</p></details>@endforeach</div>
