@pbSchema(['name' => 'article_detail.blade'])
@php
    $showCover = \App\Support\Cms\NativeBlockOptions::enabled($content, 'show_cover');
    $showMeta = \App\Support\Cms\NativeBlockOptions::enabled($content, 'show_meta');
    $showCategory = \App\Support\Cms\NativeBlockOptions::enabled($content, 'show_category');
    $articlesUrl = \App\Support\CleanUrl::to('articles', $lang);
    $homeUrl = \App\Support\CleanUrl::to('/', $lang);
    $publishedAt = $article ? ($article->published_at ?: $article->created_at) : null;
    $bodyHtml = $article ? app(\App\Support\Cms\SafeHtml::class)->clean($article->body ?: '') : '';
    $hasBody = trim(strip_tags($bodyHtml)) !== '';
    $pageCount = $hasBody ? 2 : 1;
    $authorName = trim((string) ($article?->author_name ?? ''));
    $excerpt = trim(strip_tags((string) ($article?->excerpt ?? '')));
    $categoryName = $article?->category?->title ?: 'Akademik məqalə';
@endphp

@if($article)
    <main class="article-reader-page" id="ve-page-root" data-ve-root>
        <div class="container">
            <nav class="article-reader-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ $homeUrl }}">Ana səhifə</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ $articlesUrl }}">Akademik yazılar</a>
                <i class="fa-solid fa-chevron-right"></i>
                <strong>Məqalə</strong>
            </nav>

            <section class="article-reader-shell" data-article-reader data-reader-pages="{{ $pageCount }}">
                <div class="article-reader-toolbar">
                    <div class="reader-toolbar-left">
                        <a class="reader-toolbar-link" href="{{ $articlesUrl }}">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span class="reader-toolbar-label">Geri qayıt</span>
                        </a>
                        <span class="reader-toolbar-divider" aria-hidden="true"></span>
                        <span class="reader-toolbar-page-count" data-reader-count>1 / {{ $pageCount }}</span>
                    </div>

                    <div class="reader-toolbar-center">
                        <div class="reader-toolbar-zoom" aria-label="Ölçü">
                            <button type="button" data-reader-zoom="-0.1" aria-label="Kiçilt">−</button>
                            <output data-reader-zoom-value>100%</output>
                            <button type="button" data-reader-zoom="0.1" aria-label="Böyüt">+</button>
                        </div>
                        <button type="button" class="reader-toolbar-button reader-toolbar-icon" data-reader-reset-zoom aria-label="Ölçünü sıfırla">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>

                    <div class="reader-toolbar-right">
                        <button type="button" class="reader-toolbar-button" data-reader-print>
                            <i class="fa-solid fa-download"></i>
                            <span class="reader-toolbar-label">Yüklə</span>
                        </button>
                        <button type="button" class="reader-toolbar-button" data-reader-share>
                            <i class="fa-solid fa-share-nodes"></i>
                            <span class="reader-toolbar-label">Paylaş</span>
                        </button>
                    </div>
                </div>

                <div class="article-reader-stage-wrap">
                    <div class="article-reader-stage" data-reader-stage>
                        <article class="reader-page reader-cover-page">
                            <div class="reader-cover-copy">
                                <span class="reader-kicker">{{ $categoryName }}</span>
                                <h1 data-entity="article" data-entity-id="{{ $article->id }}" data-entity-field="title">{{ $article->title }}</h1>

                                @if($showMeta)
                                    <p class="reader-authors">
                                        {{ $authorName !== '' ? $authorName : ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY') }}
                                    </p>
                                    <div class="reader-affiliations">
                                        @if($showCategory && $article->category)
                                            <span><i class="fa-solid fa-circle"></i><span data-entity="category" data-entity-id="{{ $article->category->id }}" data-entity-field="title">{{ $article->category->title }}</span></span>
                                        @endif
                                        @if($publishedAt)
                                            <span><i class="fa-solid fa-circle"></i>{{ optional($publishedAt)->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                @endif

                                @if($excerpt !== '')
                                    <div class="reader-abstract">
                                        <strong>Xülasə</strong>
                                        {{ $excerpt }}
                                    </div>
                                @endif

                                <p class="reader-keywords"><strong>Açar sözlər:</strong> {{ $categoryName }}, təcili tibb, akademik bilik, klinik yanaşma</p>
                            </div>

                            <div class="reader-cover-visual">
                                @if($showCover && $article->cover_image)
                                    <img src="{{ asset(ltrim($article->cover_image, '/')) }}" alt="{{ $article->title }}">
                                @else
                                    <span class="reader-cover-placeholder"><i class="fa-solid fa-book-medical"></i></span>
                                @endif
                                <span class="reader-cover-stamp"><i class="fa-regular fa-file-lines"></i>{{ $settings['site_name'] ?? 'ALTURAMEDIX ACADEMY' }}</span>
                            </div>

                            <span class="reader-page-number">01</span>
                        </article>

                        @if($hasBody)
                            <article class="reader-page reader-content-page">
                                <header class="reader-running-header">
                                    <span>{{ $article->title }}</span>
                                    <span>{{ $categoryName }}</span>
                                </header>
                                <div class="reader-prose" data-entity="article" data-entity-id="{{ $article->id }}" data-entity-field="body" data-entity-type="richtext">
                                    {!! $bodyHtml !!}
                                </div>
                                <span class="reader-page-number">02</span>
                            </article>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div class="article-reader-toast" data-reader-toast role="status" aria-live="polite">
        <i class="fa-solid fa-check"></i><span>Link kopyalandı.</span>
    </div>
@else
    <main class="article-reader-page"><div class="container"><div class="reader-no-body"><div><i class="fa-regular fa-folder-open"></i><strong>{{ $ui['article_not_found'] }}</strong><p>Bu məqalə mövcud deyil və ya artıq aktiv deyil.</p></div></div></div></main>
@endif

@push('scripts')
<script>
(() => {
    const reader = document.querySelector('[data-article-reader]');
    if (!reader) return;

    const stage = reader.querySelector('[data-reader-stage]');
    const zoomOutput = reader.querySelector('[data-reader-zoom-value]');
    const toast = document.querySelector('[data-reader-toast]');
    let zoom = 1;

    const renderZoom = () => {
        stage.style.setProperty('--reader-zoom', String(zoom));
        if (zoomOutput) zoomOutput.value = `${Math.round(zoom * 100)}%`;
    };

    reader.querySelectorAll('[data-reader-zoom]').forEach((button) => {
        button.addEventListener('click', () => {
            zoom = Math.max(0.7, Math.min(1.3, +(zoom + Number(button.dataset.readerZoom)).toFixed(2)));
            renderZoom();
        });
    });

    reader.querySelector('[data-reader-reset-zoom]')?.addEventListener('click', () => {
        zoom = 1;
        renderZoom();
    });

    reader.querySelector('[data-reader-print]')?.addEventListener('click', () => window.print());

    reader.querySelector('[data-reader-share]')?.addEventListener('click', async () => {
        const shareData = { title: document.title, url: window.location.href };
        try {
            if (navigator.share) {
                await navigator.share(shareData);
                return;
            }
            await navigator.clipboard.writeText(window.location.href);
            toast?.classList.add('is-visible');
            window.setTimeout(() => toast?.classList.remove('is-visible'), 2500);
        } catch (error) {
            // User-cancelled native share does not need an error message.
        }
    });

    renderZoom();
})();
</script>
@endpush