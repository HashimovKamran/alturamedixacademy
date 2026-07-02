@extends('layouts.public')

@section('title', $settings['site_name'] ?? 'ALTURAMEDIX ACADEMY')
@section('meta_description', $settings['site_description'] ?? '')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/alturamedix-home.css') }}">
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
