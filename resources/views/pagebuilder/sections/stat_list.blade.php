@pbSchema(['name' => 'stat_list.blade'])
@if($content['title']??false)<h2>{{ $content['title'] }}</h2>@endif
<div class="pb-cards pb-stats">@foreach($content['items']??[] as $item)<div class="pb-card"><i class="{{ preg_replace('/[^a-zA-Z0-9 _\-]/','',$item['icon']??'fa-solid fa-chart-line') }}"></i><strong>{{ $item['value']??'' }}</strong><p>{{ $item['title']??'' }}</p></div>@endforeach</div>
