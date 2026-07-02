@extends('layouts.public')

@section('title', $settings['site_name'] ?? 'ALTURAMEDIX ACADEMY')
@section('meta_description', $settings['site_description'] ?? '')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/alturamedix-home.css') }}">
<style>
    /* Final hero rendering override: no dark CSS layers, no crop/zoom. */
    body.aa-home-page .aa-home-hero {
        background: transparent !important;
    }

    body.aa-home-page .aa-home-hero::before,
    body.aa-home-page .aa-home-hero::after,
    body.aa-home-page .hero-slider::before,
    body.aa-home-page .hero-slider::after,
    body.aa-home-page .aa-hero-slide::before,
    body.aa-home-page .aa-hero-slide::after,
    body.aa-home-page .aa-hero-art::before,
    body.aa-home-page .aa-hero-art::after {
        content: none !important;
        display: none !important;
        background: none !important;
    }

    body.aa-home-page .aa-hero-art {
        background-color: transparent !important;
        background-position: center center !important;
        background-size: 100% 100% !important;
        background-repeat: no-repeat !important;
        filter: none !important;
        mix-blend-mode: normal !important;
    }

    body.aa-home-page .aa-hero-art.jquery-ripples,
    body.aa-home-page .aa-hero-art.jquery-ripples canvas {
        background-color: transparent !important;
        filter: none !important;
        opacity: 1 !important;
        mix-blend-mode: normal !important;
    }
</style>
@endpush

@section('content')
@include('public.partials.composition')
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/jquery.ripples@0.6.3/dist/jquery.ripples-min.js"></script>
<script defer src="{{ asset('js/home-bione-water-ripples.js') }}"></script>
<script>
(() => {
    const header = document.getElementById('siteHeader');
    if (!header) return;

    const syncHeaderState = () => {
        header.classList.toggle('aa-header-scrolled', window.scrollY > 10);
    };

    syncHeaderState();
    window.addEventListener('scroll', syncHeaderState, { passive: true });
})();
</script>
@endpush
