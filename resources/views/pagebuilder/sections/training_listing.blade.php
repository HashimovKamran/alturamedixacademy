@pbSchema(['name' => 'training_listing.blade'])
@php
    $limit = max(1, min(6, (int) ($content['limit'] ?? 3)));
    $items = \App\Models\Training::query()->forLanguage($lang)->active()->orderBy('training_date')->orderBy('sort_order')->limit($limit)->get();
    $date = app(\App\Services\Site\DateFormatter::class);
@endphp
@if(($activePage ?? '') === 'index')
<section class="aa-training-panel">
    <div class="aa-section-heading"><h2 data-inline-field="title">{{ $content['title'] ?? ($siteSettings['section_trainings'] ?? ($ui['courses_and_conferences'] ?? 'Təlim və konfranslar')) }}</h2><a href="{{ \App\Support\CleanUrl::to('trainings', $lang) }}">{{ $siteSettings['all_view'] ?? ($ui['view_all'] ?? 'Hamısına bax') }} <i class="fa-solid fa-arrow-right"></i></a></div>
    <div class="aa-training-list">
        @forelse($items as $item)
            <article class="aa-training-card">
                <div class="aa-training-cover">
                    @if($item->cover_image)<img src="{{ asset(ltrim($item->cover_image, '/')) }}" alt="{{ $item->title }}">@else<span><i class="fa-solid fa-graduation-cap"></i></span>@endif
                </div>
                <div class="aa-training-copy">
                    <h3 data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</h3>
                    @if($item->training_date)<p><i class="fa-regular fa-calendar"></i>{{ $date->format($item->training_date, $lang) }}</p>@endif
                    @if($item->location)<p data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="location"><i class="fa-solid fa-location-dot"></i>{{ $item->location }}</p>@endif
                </div>
            </article>
        @empty
            <div class="aa-empty-state">{{ $ui['no_trainings'] ?? 'Hazırda təlim yoxdur.' }}</div>
        @endforelse
    </div>
</section>
@else
<main class="trainings-page"><div class="container">
    <section class="trainings-head"><h1 data-inline-field="title">{{ $content['title'] ?? ($siteSettings['section_trainings'] ?? 'Təlimlər') }}</h1></section>
    <section class="trainings-grid">
        @forelse($items as $item)
            <article class="training-card">
                @if($item->cover_image)<img src="{{ asset(ltrim($item->cover_image, '/')) }}" alt="{{ $item->title }}">@endif
                @if($item->training_date)<div class="training-date">{{ $date->format($item->training_date, $lang) }}</div>@endif
                <h2 data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</h2>
                @if($item->location)<p data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="location">{{ $item->location }}</p>@endif
            </article>
        @empty
            <div class="trainings-empty">{{ $ui['no_trainings'] ?? '' }}</div>
        @endforelse
    </section>
</div></main>
@endif