@extends('layouts.admin')
@section('title', 'İdarə paneli')
@section('page_title', 'İdarə paneli')

@push('styles')
<style>
    .dash-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .dash-stat{position:relative;overflow:hidden;background:#fff;border:1px solid var(--admin-line-2);border-radius:16px;padding:16px;min-height:132px;box-shadow:0 12px 32px rgba(61,125,131,.05);transition:.18s ease}
    .dash-stat:hover{transform:translateY(-2px);border-color:#cfe7e3;box-shadow:0 18px 42px rgba(61,125,131,.095)}
    .dash-stat i{display:inline-grid;place-items:center;width:auto;height:auto;border-radius:0;background:transparent;color:#6f7d82;font-size:19px;line-height:1}
    .dash-stat:nth-child(2n) i,.dash-stat:nth-child(3n) i,.dash-stat:nth-child(4n) i{background:transparent;color:#6f7d82}
    .dash-stat strong{display:block;margin-top:18px;font-size:28px;line-height:1;font-weight:900;color:#111;letter-spacing:-.025em}
    .dash-stat span{display:block;margin-top:7px;color:#353a3f;font-size:13px;font-weight:900}
    .dash-stat small{display:block;margin-top:4px;color:var(--admin-muted);font-size:12px;font-weight:750}
    .dash-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr);gap:18px;align-items:start}
    .activity-item{display:grid;grid-template-columns:40px minmax(0,1fr) auto;gap:12px;align-items:center;padding:13px 0;border-bottom:1px solid var(--admin-line-2)}
    .activity-item:last-child{border-bottom:0}
    .activity-icon{width:34px;height:34px;border-radius:10px;background:transparent;color:#6f7d82;display:grid;place-items:center}
    .activity-icon i{font-size:18px;line-height:1}
    .activity-item strong{display:block;color:#111;font-weight:900;font-size:13px}
    .activity-item span{display:block;margin-top:3px;color:var(--admin-muted);font-size:12px;font-weight:720;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .activity-time{color:var(--admin-muted);font-size:11px;font-weight:850;text-align:right}
    .mini-list{display:grid;gap:9px}
    .mini-row{display:flex;justify-content:space-between;gap:14px;padding:10px 12px;border:1px solid var(--admin-line-2);border-radius:12px;background:linear-gradient(135deg,#fff 0%,#f4fbfa 100%)}
    .mini-row span{color:var(--admin-muted);font-size:13px;font-weight:800}
    .mini-row strong{font-weight:900}
    @media(max-width:1200px){.dash-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.dash-grid{grid-template-columns:1fr}}
    @media(max-width:760px){.dash-stats{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $hiddenModuleUrls = [
        route('admin.modules.index', ['module' => 'pages']),
        route('admin.modules.index', ['module' => 'blocks']),
    ];
    $visibleStats = collect($stats)->reject(fn ($stat) => in_array($stat['url'] ?? null, $hiddenModuleUrls, true));
@endphp
<section class="dash-stats">
    @foreach($visibleStats as $stat)
        <a class="dash-stat" href="{{ $stat['url'] }}">
            <i class="{{ $stat['icon'] }}"></i>
            <strong>{{ $stat['value'] }}</strong>
            <span>{{ $stat['title'] }}</span>
            <small>{{ $stat['note'] }}</small>
        </a>
    @endforeach
</section>

<section class="dash-grid">
    <div class="card">
        <h2>Son aktivlik</h2>
        @forelse($logs as $log)
            @php
                $module = data_get($log, 'module', '-');
                $action = data_get($log, 'action', 'update');
                $description = data_get($log, 'description', '');
                $createdAt = data_get($log, 'created_at');
                $icons = [
                    'articles' => 'ti ti-news',
                    'categories' => 'ti ti-category',
                    'sliders' => 'ti ti-photo',
                    'menus' => 'ti ti-menu-2',
                    'pages' => 'ti ti-file-text',
                    'gallery' => 'ti ti-photo',
                    'ads' => 'ti ti-ad-2',
                    'partners' => 'ti ti-users-group',
                    'blocks' => 'ti ti-box',
                    'users' => 'ti ti-users',
                    'contact' => 'ti ti-mail',
                ];
            @endphp
            <div class="activity-item">
                <div class="activity-icon"><i class="{{ $icons[$module] ?? 'ti ti-history' }}"></i></div>
                <div>
                    <strong>{{ $module }} / {{ $action }}</strong>
                    <span>{{ $description ?: 'Sistem qeydi' }}</span>
                </div>
                <div class="activity-time">
                    {{ $createdAt ? \Illuminate\Support\Carbon::parse($createdAt)->format('d.m.Y H:i') : '-' }}
                </div>
            </div>
        @empty
            <p class="muted">Hələ admin log yoxdur.</p>
        @endforelse
    </div>

    <div class="card">
        <h2>Qısa göstəricilər</h2>
        <div class="mini-list">
            <div class="mini-row"><span>Aktiv məqalələr</span><strong>{{ $activeStats['activeArticles'] }}</strong></div>
            <div class="mini-row"><span>Passiv məqalələr</span><strong>{{ $activeStats['inactiveArticles'] }}</strong></div>
            <div class="mini-row"><span>Aktiv sliderlər</span><strong>{{ $activeStats['activeSliders'] }}</strong></div>
            <div class="mini-row"><span>Aktiv qalereya</span><strong>{{ $activeStats['activeGallery'] }}</strong></div>
            <div class="mini-row"><span>Aktiv reklamlar</span><strong>{{ $activeStats['activeAds'] }}</strong></div>
        </div>
    </div>
</section>
@endsection
