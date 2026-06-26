@extends('layouts.public')
@php
    $ui = \App\Support\PublicUiText::all($lang);
@endphp
@section('title', $ui['profile'] . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))
@push('styles')
<style>.profile-page{min-height:60vh;background:#f4f7fb;padding:46px 0 60px}.profile-card{background:#fff;border:1px solid #dbe4ee;border-radius:28px;padding:30px;box-shadow:0 22px 70px rgba(7,23,40,.08)}.profile-head{display:flex;gap:18px;align-items:center;margin-bottom:24px}.avatar{width:76px;height:76px;border-radius:22px;background:#071728;color:#ff8a1c;display:grid;place-items:center;font-size:28px;font-weight:900;overflow:hidden}.avatar img{width:100%;height:100%;object-fit:cover}.profile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.profile-field{border:1px solid #e4ebf4;border-radius:18px;padding:16px}.profile-field span{display:block;color:#64748b;font-weight:800;font-size:13px}.profile-field strong{display:block;margin-top:6px}@media(max-width:700px){.profile-grid{grid-template-columns:1fr}.profile-head{align-items:flex-start;flex-direction:column}}</style>
@endpush
@section('content')
@include('public.partials.composition')
@endsection
