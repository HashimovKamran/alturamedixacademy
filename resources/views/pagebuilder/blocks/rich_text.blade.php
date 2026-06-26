@pbSchema(['name' => 'rich_text.blade'])
@if($content['title']??false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif
<div class="pb-body" data-inline-field="html" data-inline-type="richtext">{!! app(\App\Support\Cms\SafeHtml::class)->clean($content['html']??'') !!}</div>
