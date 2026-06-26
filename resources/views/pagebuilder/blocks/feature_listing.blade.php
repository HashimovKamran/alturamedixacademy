@pbSchema(['name' => 'feature_listing.blade'])
@php
    $items=\App\Models\Feature::query()->forLanguage($lang)->active()->orderBy('sort_order')->get();
    $support=\App\Models\Block::query()->forLanguage($lang)->active()->where('block_key','support')->first();
@endphp
<section class="content-section"><div class="container feature-wide-section">
    <div class="section-head">@if($content['title'] ?? false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif</div>
    <div class="feature-grid">@foreach($items as $item)<a href="{{ \App\Support\CleanUrl::to($item->url ?: '#', $lang) }}" class="feature-card"><i class="{{ $item->icon_class ?: 'fa-solid fa-circle-info' }}"></i><span><strong data-entity="feature" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</strong><small data-entity="feature" data-entity-id="{{ $item->id }}" data-entity-field="description">{{ $item->description }}</small></span><i class="fa-solid fa-arrow-right"></i></a>@endforeach</div>
    @if(\App\Support\Cms\NativeBlockOptions::enabled($content,'show_support') && $support)<div class="support-box"><div><i class="fa-solid fa-headset"></i><span><strong data-entity="block" data-entity-id="{{ $support->id }}" data-entity-field="title">{{ $support->title }}</strong><small data-entity="block" data-entity-id="{{ $support->id }}" data-entity-field="body">{{ $support->body }}</small></span></div>@if($support->button_text)<a href="{{ \App\Support\CleanUrl::to($support->button_url ?: '#', $lang) }}" class="btn btn-light">{{ $support->button_text }} <i class="fa-solid fa-arrow-right"></i></a>@endif</div>@endif
</div></section>
