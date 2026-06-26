@extends('layouts.public')

@section('title', $settings['site_name'] ?? 'ALTURAMEDIX ACADEMY')
@section('meta_description', $settings['site_description'] ?? '')

@section('content')
@include('public.partials.composition')
@endsection
