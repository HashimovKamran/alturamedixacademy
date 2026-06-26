@extends('layouts.public')

@section('title', $page->title . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))
@section('meta_description', $page->meta_description ?? '')

@push('styles')
<style>
.cert-page{padding:46px 0 66px;background:radial-gradient(circle at 16% 0%,rgba(255,138,28,.08),transparent 28%),linear-gradient(180deg,#f7fbff 0%,#f3f6fa 100%)}
.cert-hero{position:relative;overflow:hidden;background:linear-gradient(135deg,#061727,#0b2d4b);color:#fff;border-radius:28px;padding:42px;box-shadow:0 26px 76px rgba(7,23,40,.16);display:grid;grid-template-columns:minmax(0,1fr) 420px;gap:38px;align-items:center;animation:certIn .35s ease both}
.cert-hero::before{content:"";position:absolute;inset:auto -110px -170px auto;width:360px;height:360px;border-radius:50%;background:rgba(255,138,28,.16)}
.cert-hero::after{content:"";position:absolute;right:26%;top:-90px;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.05)}
@keyframes certIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.cert-hero-copy{position:relative;z-index:2;max-width:690px}
.cert-hero h1{margin:0;font-size:44px;line-height:1.12;font-weight:900;letter-spacing:-.025em}
.cert-hero p{margin:16px 0 0;color:#d7e5f2;font-size:18px;font-weight:750;line-height:1.75}
.cert-hero-points{margin-top:26px;display:flex;flex-wrap:wrap;gap:10px}
.cert-hero-points span{display:inline-flex;align-items:center;gap:8px;color:#e8f3ff;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:999px;padding:10px 13px;font-size:13px;font-weight:850}
.cert-hero-points i{color:#ff8a1c}
.cert-check-box{position:relative;z-index:2;background:#fff;border:1px solid rgba(219,228,238,.88);border-radius:24px;box-shadow:0 24px 70px rgba(0,0,0,.18);padding:28px;color:#071728}
.cert-check-box::before{content:"";position:absolute;left:28px;right:28px;top:0;height:4px;background:#ff8a1c;border-radius:0 0 999px 999px}
.cert-check-head{display:flex;align-items:flex-start;gap:14px;margin-bottom:18px}
.cert-check-icon{width:52px;height:52px;border-radius:16px;background:#fff5eb;color:#ff8a1c;display:grid;place-items:center;font-size:23px;flex:0 0 auto}
.cert-check-box h2{margin:0;font-size:27px;line-height:1.18;font-weight:900;color:#071728;letter-spacing:-.02em}
.cert-check-box p{margin:8px 0 0;color:#64748b;line-height:1.6;font-weight:750}
.cert-form{display:grid;gap:12px}
.cert-form label{font-size:13px;font-weight:900;color:#071728}
.cert-form input{width:100%;height:54px;border:1px solid #dbe4ee;border-radius:15px;padding:0 16px;font-family:inherit;font-size:15px;font-weight:850;outline:0;text-transform:uppercase;background:#fbfdff}
.cert-form input:focus{border-color:#ff8a1c;background:#fff;box-shadow:0 0 0 4px rgba(255,138,28,.12)}
.cert-form .btn{height:56px;border-radius:15px;width:100%;font-size:16px}
.cert-layout{margin-top:24px}
.cert-result{background:#fff;border:1px solid #dbe4ee;border-radius:26px;box-shadow:0 22px 62px rgba(7,23,40,.08);padding:0;overflow:hidden;display:grid;grid-template-columns:310px minmax(0,1fr)}
.cert-result-head{padding:28px;display:flex;align-items:flex-start;gap:16px;color:#fff;position:relative;overflow:hidden}
.cert-result-head::after{content:"";position:absolute;right:-70px;bottom:-90px;width:210px;height:210px;border-radius:50%;background:rgba(255,255,255,.12)}
.cert-result-head.valid{background:linear-gradient(135deg,#15803d,#16a34a)}
.cert-result-head.expired{background:linear-gradient(135deg,#c65b00,#f97316)}
.cert-result-head.revoked,.cert-result-head.unknown{background:linear-gradient(135deg,#991b1b,#dc2626)}
.cert-result-head i{width:54px;height:54px;border-radius:17px;background:rgba(255,255,255,.16);display:grid;place-items:center;font-size:24px;position:relative;z-index:1;flex:0 0 auto}
.cert-result-head div{position:relative;z-index:1;min-width:0}
.cert-result-head strong{display:block;font-size:25px;line-height:1.15;font-weight:900}
.cert-result-head span{display:inline-flex;margin-top:9px;color:#fff;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16);border-radius:999px;padding:6px 10px;font-size:13px;font-weight:850;word-break:break-all}
.cert-result-body{padding:26px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;background:linear-gradient(180deg,#fff 0%,#fbfdff 100%)}
.cert-row{display:grid;gap:7px;border:1px solid #eef2f7;border-radius:16px;padding:14px 15px;color:#10263a;font-weight:850;background:#fff;min-width:0}
.cert-row span{color:#64748b;font-weight:900;font-size:12px}
.cert-row b{font-size:15px;line-height:1.35;word-break:break-word}
.cert-document-action{grid-column:1/-1;height:54px;border-radius:15px;justify-content:center}
.cert-not-found{background:#fff0f0;border:1px solid #ffcaca;color:#991b1b;border-radius:18px;padding:18px;font-weight:850;line-height:1.7}
.cert-result-stack{display:grid;gap:18px}
@media(max-width:1000px){.cert-hero{grid-template-columns:1fr;padding:32px}.cert-hero h1{font-size:35px}.cert-check-box{box-shadow:0 20px 52px rgba(0,0,0,.14)}.cert-result{grid-template-columns:1fr}.cert-result-head{padding:24px}.cert-result-body{padding:22px}}
@media(max-width:640px){.cert-page{padding:24px 0 46px}.cert-hero{padding:24px;border-radius:22px;gap:24px}.cert-hero h1{font-size:30px}.cert-hero p{font-size:16px}.cert-hero-points{display:none}.cert-check-box{padding:22px;border-radius:20px}.cert-check-box::before{left:22px;right:22px}.cert-check-head{display:block}.cert-check-icon{margin-bottom:12px}.cert-result-body{grid-template-columns:1fr;padding:16px}.cert-result-head strong{font-size:22px}}
</style>
@endpush

@section('content')
@include('public.partials.composition')
@endsection
