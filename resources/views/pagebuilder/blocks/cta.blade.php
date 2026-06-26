@pbSchema(['name' => 'cta.blade'])
<div class="pb-cta"><h2>{{ $content['title']??'' }}</h2>@if($content['text']??false)<p>{{ $content['text'] }}</p>@endif @if($content['button_text']??false)<a class="pb-btn" href="{{ \App\Support\Cms\SafeUrl::clean($content['button_url']??'#') }}">{{ $content['button_text'] }}</a>@endif</div>
