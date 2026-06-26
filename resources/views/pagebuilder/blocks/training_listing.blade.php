@pbSchema(['name' => 'training_listing.blade'])
@php
    $limit = max(1, min(24, (int) ($content['limit'] ?? 6)));
    $items = \App\Models\Training::query()->forLanguage($lang)->active()->orderBy('training_date')->limit($limit)->get();
    $date = app(\App\Services\Site\DateFormatter::class);
@endphp
@if($block->page_key === 'index')
<div class="training-panel">
    <div class="panel-head"><h3 data-inline-field="title">{{ $content['title'] ?? ($siteSettings['section_trainings'] ?? ($ui['courses_and_conferences'] ?? 'Təlimlər')) }}</h3><a href="{{ \App\Support\CleanUrl::to('trainings', $lang) }}">{{ $siteSettings['all_view'] ?? ($ui['view_all'] ?? 'Hamısına bax') }} <i class="fa-solid fa-arrow-right"></i></a></div>
    @foreach($items as $item)
        @php [$day, $month] = $date->shortMonth($item->training_date, $lang); @endphp
        <div class="training-item">
            <div class="training-date"><strong>{{ $day }}</strong><span>{{ $month }}</span></div>
            <div class="training-info"><strong data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</strong><small data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="location">{{ $item->location }}</small></div>
            @if($item->register_url)<a href="{{ \App\Support\Cms\SafeUrl::clean($item->register_url) }}">{{ $siteSettings['btn_register'] ?? ($ui['register'] ?? 'Qeydiyyat') }}</a>@else<button type="button" data-auth-open="register" onclick="return AAAuthModalOpen('register')">{{ $siteSettings['btn_register'] ?? ($ui['register'] ?? 'Qeydiyyat') }}</button>@endif
        </div>
    @endforeach
    <a class="all-trainings" href="{{ \App\Support\CleanUrl::to('trainings', $lang) }}">{{ $siteSettings['section_trainings_all'] ?? '' }} <i class="fa-solid fa-arrow-right"></i></a>
</div>
@else
<main class="trainings-page"><div class="container">
    <section class="trainings-head"><h1 data-inline-field="title">{{ $content['title'] ?? ($siteSettings['section_trainings'] ?? 'Təlimlər') }}</h1></section>
    <section class="trainings-grid">@forelse($items as $item)<article class="training-card"><div class="training-date">{{ $date->format($item->training_date, $lang) }}</div><h2 data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="title">{{ $item->title }}</h2><p data-entity="training" data-entity-id="{{ $item->id }}" data-entity-field="location">{{ $item->location }}</p>@if($item->register_url)<a class="btn btn-primary" href="{{ \App\Support\Cms\SafeUrl::clean($item->register_url) }}">{{ $ui['register'] ?? 'Qeydiyyat' }}</a>@endif</article>@empty<div class="trainings-empty">{{ $ui['no_trainings']??'' }}</div>@endforelse</section>
</div></main>
@endif
