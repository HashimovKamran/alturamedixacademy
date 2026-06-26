@pbSchema(['name' => 'feature_listing.blade'])
@php($items = \App\Models\Feature::query()->forLanguage($lang)->active()->orderBy('sort_order')->get())
@if($items->isNotEmpty())
<section class="aa-feature-section">
    <div class="container">
        <div class="aa-section-heading aa-feature-heading"><h2 data-inline-field="title">{{ ($content['title'] ?? '') ?: ($siteSettings['section_features'] ?? 'Akademik imkanlarımız') }}</h2></div>
        <div class="aa-feature-grid">
            @foreach($items as $item)
                <a href="{{ \App\Support\CleanUrl::to($item->url ?: '#', $lang) }}" class="aa-feature-card">
                    <i class="{{ $item->icon_class ?: 'fa-solid fa-circle-info' }}"></i>
                    <span><strong data-entity="feature" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</strong><small data-entity="feature" data-entity-id="{{ $item->id }}" data-entity-field="description">{{ $item->description }}</small></span>
                    <b><i class="fa-solid fa-arrow-right"></i></b>
                </a>
            @endforeach
        </div>
        <div class="aa-feature-more"><a href="{{ \App\Support\CleanUrl::to($siteSettings['features_all_url'] ?? '#', $lang) }}">{{ $siteSettings['features_all_text'] ?? ($ui['view_all'] ?? 'Bütünə bax') }} <i class="fa-solid fa-arrow-right"></i></a></div>
    </div>
</section>
@endif