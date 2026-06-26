@pbSchema(['name' => 'home_content_grid.blade'])
@php
    $mainChildren = $children->filter(fn ($child) => $child['block']->slot_key === 'main');
    $sidebarChildren = $children->filter(fn ($child) => $child['block']->slot_key === 'sidebar');
    $articleChildren = $mainChildren->filter(fn ($child) => $child['block']->type === 'article_listing');
    $journalChildren = $mainChildren->filter(fn ($child) => $child['block']->type === 'home_journal');
    $lowerChildren = $mainChildren->filter(fn ($child) => in_array($child['block']->type, ['feature_listing', 'partner_listing'], true));
    // category_listing is now rendered inside article_listing on the homepage, so it must not appear twice.
    $otherMainChildren = $mainChildren->filter(fn ($child) => ! in_array($child['block']->type, ['article_listing', 'category_listing', 'home_journal', 'feature_listing', 'partner_listing'], true));
@endphp
<section class="aa-home-content">
    <div class="container aa-home-content-container">
        <div class="aa-home-grid">
            <main class="aa-home-main">
                @foreach($articleChildren as $child)@include('pagebuilder.partials.node', ['node' => $child, 'embedded' => true])@endforeach
                @foreach($otherMainChildren as $child)@include('pagebuilder.partials.node', ['node' => $child, 'embedded' => true])@endforeach
                @foreach($journalChildren as $child)@include('pagebuilder.partials.node', ['node' => $child, 'embedded' => true])@endforeach
            </main>
            <aside class="aa-home-sidebar">
                @foreach($sidebarChildren as $child)@include('pagebuilder.partials.node', ['node' => $child, 'embedded' => true])@endforeach
            </aside>
        </div>
    </div>
</section>
@foreach($lowerChildren as $child)@include('pagebuilder.partials.node', ['node' => $child, 'embedded' => true])@endforeach