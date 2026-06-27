@extends('layouts.public')

@section('title', $settings['site_name'] ?? 'ALTURAMEDIX ACADEMY')
@section('meta_description', $settings['site_description'] ?? '')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-reference.css') }}">
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-final.css') }}">
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-match.css') }}">
@endpush

@section('content')
@include('public.partials.composition')
@endsection

@push('scripts')
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
