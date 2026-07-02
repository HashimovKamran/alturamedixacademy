@pbSchema(['name' => 'article_detail.blade'])
<link rel="stylesheet" href="{{ asset('css/article-image-view.css') }}">
@php
    $articlesUrl = \App\Support\CleanUrl::to('articles', $lang);
    $homeUrl = \App\Support\CleanUrl::to('/', $lang);
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

            <section class="article-reader-shell" data-article-reader>
                <div class="article-reader-toolbar">
                    <div class="reader-toolbar-left">
                        <a class="reader-toolbar-link" href="{{ $articlesUrl }}">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span class="reader-toolbar-label">Geri qayıt</span>
                        </a>
                        <span class="reader-toolbar-divider" aria-hidden="true"></span>
                        <span class="reader-toolbar-page-count">1 / 1</span>
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
                        <article class="reader-page reader-image-page">
                            @if($article->cover_image)
                                <img src="{{ asset(ltrim($article->cover_image, '/')) }}" alt="{{ $article->title }}">
                            @else
                                <div class="reader-image-empty">
                                    <div>
                                        <i class="fa-regular fa-image"></i>
                                        <strong>Məqalə şəkli yüklənməyib</strong>
                                        <span>Bu məqalə üçün admin paneldən şəkil əlavə edin.</span>
                                    </div>
                                </div>
                            @endif
                            <span class="reader-page-number">01</span>
                        </article>
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
            // Native share cancellation or clipboard denial needs no visible error.
        }
    });

    renderZoom();
})();
</script>
@endpush