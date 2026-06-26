@pbSchema(['name' => 'button_group.blade'])
<div class="pb-button-group">@foreach($content['items']??[] as $item)<a class="pb-btn {{ ($item['style']??'')==='light'?'is-light':'' }}" href="{{ \App\Support\Cms\SafeUrl::clean($item['url']??'#') }}">{{ $item['title']??'' }}</a>@endforeach</div>
