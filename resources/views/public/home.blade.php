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
