@extends('layouts.public')

@section('title', $settings['site_name'] ?? 'ALTURAMEDIX ACADEMY')
@section('meta_description', $settings['site_description'] ?? '')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-reference.css') }}">
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-final.css') }}">
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-match.css') }}">
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-slider-reference.css') }}">
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-ads-size.css') }}">
<link rel="stylesheet" href="{{ asset('css/home-bione-water-ripples.css') }}">
<link rel="stylesheet" href="{{ asset('css/alturamedix-home-partners-size.css') }}">
@endpush

@section('content')
<style data-aa-home-header-actions>
body.aa-home-page .aa-site-header .aa-search-button{box-sizing:border-box!important;width:38px!important;min-width:38px!important;height:38px!important;min-height:38px!important;flex:0 0 38px!important;padding:0!important;border:1px solid rgba(255,255,255,.35)!important;border-radius:50%!important;background:rgba(3,24,44,.18)!important}
body.aa-home-page .aa-site-header .aa-search-label{display:none!important}
body.aa-home-page .aa-site-header .aa-login-button{height:38px!important;min-height:38px!important;padding:0 15px!important;border-radius:9px!important;border-color:rgba(255,255,255,.38)!important;background:rgba(3,24,44,.18)!important;color:#fff!important;font-size:12px!important}
body.aa-home-page .aa-site-header .aa-language-switch{min-height:38px!important;gap:0!important}
body.aa-home-page .aa-site-header .aa-language-switch i{display:none!important}
body.aa-home-page .aa-site-header .aa-language-switch select{height:38px!important;min-width:42px!important;padding:0 15px 0 0!important;border:0!important;background:transparent!important;color:#fff!important}
body.aa-home-page .aa-site-header .aa-language-switch:after{right:0!important}
</style>
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
