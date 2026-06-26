@pbSchema(['name' => 'cards.blade'])
@if($content['title']??false)<h2>{{ $content['title'] }}</h2>@endif
<div class="pb-cards">@foreach($content['items']??[] as $item)<a class="pb-card" href="{{ \App\Support\Cms\SafeUrl::clean($item['url']??'#') }}"><i class="{{ preg_replace('/[^a-zA-Z0-9 _\-]/','',$item['icon']??'fa-solid fa-circle-check') }}"></i><strong>{{ $item['title']??'' }}</strong>@if($item['text']??false)<p>{{ $item['text'] }}</p>@endif</a>@endforeach</div>
