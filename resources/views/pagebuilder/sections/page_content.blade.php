@pbSchema(['name' => 'page_content.blade'])
@php
 $title=\App\Support\Cms\NativeBlockOptions::text($content,'title',$page->title??'');
 $subtitle=\App\Support\Cms\NativeBlockOptions::text($content,'subtitle',$page->subtitle??'');
 $html=trim((string)($content['html']??''))!==''?$content['html']:($page->body??'');
 $image=trim((string)$block->image_path)?:($page->image_path??'');
@endphp
<main class="simple-page" id="ve-page-root" data-ve-root><div class="container"><article class="page-box page-box-{{ $page->page_key ?? '' }} {{ !\App\Support\Cms\NativeBlockOptions::enabled($content,'show_image')?'no-cover':'' }}"><div class="page-content"><h1 data-inline-field="title">{{ $title }}</h1>@if($subtitle)<div class="sub" data-inline-field="subtitle">{{ $subtitle }}</div>@endif<div class="body-text" data-inline-field="html" data-inline-type="richtext">{!! app(\App\Support\Cms\SafeHtml::class)->clean($html) !!}</div></div>@if(\App\Support\Cms\NativeBlockOptions::enabled($content,'show_image'))<div class="page-cover {{ $image?'has-photo':'has-brand' }}">@if($image)<img class="page-cover-photo" src="{{ asset(ltrim($image,'/')) }}" alt="{{ $title }}">@else<div class="page-cover-brand"><span><strong>{{ $page->subtitle ?: ($siteSettings['site_name']??'ALTURAMEDIX ACADEMY') }}</strong>@if(!empty($siteSettings['site_description']))<small>{{ $siteSettings['site_description'] }}</small>@endif</span></div>@endif</div>@endif</article></div></main>
