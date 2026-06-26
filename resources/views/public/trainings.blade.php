@extends('layouts.public')

@section('title', $page->title . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))
@section('meta_description', $page->meta_description ?? '')

@push('styles')
<style>
    .trainings-page{padding:46px 0 60px;background:#f4f7fb}
    .trainings-head{margin-bottom:24px}
    .trainings-head h1{margin:0;color:#071728;font-size:40px;line-height:1.18;font-weight:900}
    .trainings-head p{margin:10px 0 0;color:#64748b;font-size:17px;font-weight:750;max-width:780px;line-height:1.7}
    .trainings-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
    .training-card{background:#fff;border:1px solid #dbe4ee;border-radius:22px;padding:22px;box-shadow:0 18px 55px rgba(7,23,40,.06);display:grid;gap:13px}
    .training-date{display:inline-flex;width:max-content;gap:8px;align-items:center;border-radius:999px;background:#fff4e8;color:#c65b00;padding:7px 11px;font-size:12px;font-weight:900}
    .training-card h2{margin:0;color:#071728;font-size:20px;line-height:1.3;font-weight:900}
    .training-card p{margin:0;color:#64748b;font-weight:750;line-height:1.65}
    .training-card a{width:max-content}
    .trainings-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:22px;padding:34px;text-align:center;color:#64748b;font-weight:850}
    @media(max-width:980px){.trainings-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:640px){.trainings-grid{grid-template-columns:1fr}.trainings-head h1{font-size:30px}}
</style>
@endpush

@section('content')
@include('public.partials.composition')
@endsection
