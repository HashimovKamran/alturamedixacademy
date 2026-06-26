@pbSchema(['name' => 'home_journal.blade'])
@php($journal = \App\Models\Block::query()->forLanguage($lang)->active()->where('block_key', 'journal')->first())
@if($journal)
<section class="aa-journal-promo">
    <div class="aa-journal-cover">
        @if($journal->image_path)
            <img src="{{ asset(ltrim($journal->image_path, '/')) }}" alt="{{ $journal->title }}">
        @else
            <span>MEDIPICENT JOURNAL</span>
        @endif
    </div>
    <div class="aa-journal-copy">
        <h2 data-entity="block" data-entity-id="{{ $journal->id }}" data-entity-field="title">{{ $journal->title }}</h2>
        @if($journal->subtitle)<span data-entity="block" data-entity-id="{{ $journal->id }}" data-entity-field="subtitle">{{ $journal->subtitle }}</span>@endif
        @if($journal->body)<p data-entity="block" data-entity-id="{{ $journal->id }}" data-entity-field="body">{{ $journal->body }}</p>@endif
        @if($journal->button_text)
            <a href="{{ \App\Support\CleanUrl::to($journal->button_url ?: '#', $lang) }}" class="aa-journal-button"><span data-entity="block" data-entity-id="{{ $journal->id }}" data-entity-field="button_text">{{ $journal->button_text }}</span><i class="fa-solid fa-arrow-right"></i></a>
        @endif
    </div>
    <div class="aa-journal-orbit" aria-hidden="true"></div>
</section>
@endif