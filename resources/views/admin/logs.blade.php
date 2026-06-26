@extends('layouts.admin')
@section('title', 'Əməliyyat logları')
@section('page_title', 'Əməliyyat logları')

@push('styles')
<style>
    .log-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
    .log-stat h2{margin:0 0 6px;font-size:28px;letter-spacing:-.03em}
    .log-filter-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px}
    .log-filter-head h2{margin:0;font-size:18px}
    .log-filter-head span{font-size:12px;font-weight:850;color:var(--admin-muted)}
    .log-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
    .log-filter-grid .wide{grid-column:1/-1}
    .log-table td{vertical-align:middle}
    .log-description{max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .log-detail-backdrop{position:fixed;inset:0;z-index:140;display:none;place-items:center;padding:24px;background:rgba(15,23,42,.34);backdrop-filter:blur(8px)}
    .log-detail-backdrop.is-open{display:grid}
    .log-detail-panel{width:min(720px,calc(100vw - 32px));background:#fff;border:1px solid var(--admin-line);border-radius:20px;box-shadow:0 28px 90px rgba(17,24,39,.18);padding:26px}
    .log-detail-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px}
    .log-detail-head h2{margin:0;font-size:22px;font-weight:900;letter-spacing:-.02em}
    .log-detail-close{width:40px;height:40px;border-radius:13px;border:1px solid var(--admin-line);display:grid;place-items:center;background:#fff;color:#111;cursor:pointer;font-size:18px}
    .log-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .log-detail-item{border:1px solid var(--admin-line);border-radius:14px;background:#f8fbfa;padding:13px 14px;min-width:0}
    .log-detail-item span{display:block;margin-bottom:5px;font-size:11px;font-weight:900;color:var(--admin-muted)}
    .log-detail-item strong{display:block;font-size:14px;line-height:1.35;word-break:break-word}
    .log-detail-item.full{grid-column:1/-1}
    .log-detail-description{margin:0;white-space:pre-wrap;font-weight:750;color:#1f2933;line-height:1.55}
    body.log-detail-open{overflow:hidden}
    @media(max-width:900px){.log-stats,.log-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.log-table{display:block;overflow:auto}}
    @media(max-width:640px){.log-stats,.log-filter-grid,.log-detail-grid{grid-template-columns:1fr}.log-detail-panel{padding:20px}.log-description{max-width:260px}}
</style>
@endpush

@section('content')
@if(session('status'))<div class="alert alert-ok">{{ session('status') }}</div>@endif

<div class="log-stats">
    <div class="card log-stat"><h2>{{ $stats['total'] }}</h2><div class="muted">Ümumi log</div></div>
    <div class="card log-stat"><h2>{{ $stats['today'] }}</h2><div class="muted">Bugünkü log</div></div>
    <div class="card log-stat"><h2>{{ $stats['creates'] }}</h2><div class="muted">Yaradılma əməliyyatları</div></div>
    <div class="card log-stat"><h2>{{ $stats['deletes'] }}</h2><div class="muted">Silmə əməliyyatları</div></div>
</div>

<div class="card">
    <div class="log-filter-head">
        <h2>Axtarış və filter</h2>
        <span>Nəticələr seçimə görə avtomatik yenilənir</span>
    </div>
    <form method="get" class="log-filter-grid" data-log-filter-form>
        <div>
            <label>Modul</label>
            <select name="module" data-log-filter-control>
                <option value="">Hamısı</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ $module }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Əməliyyat</label>
            <select name="action" data-log-filter-control>
                <option value="">Hamısı</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Başlanğıc</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" data-log-filter-control>
        </div>
        <div>
            <label>Son</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" data-log-filter-control>
        </div>
        <div class="wide">
            <label>Axtarış</label>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="İstifadəçi, açıqlama, obyekt, IP..." data-log-search>
        </div>
    </form>
</div>

<div class="card">
    <table class="log-table">
        <thead>
            <tr>
                <th>Tarix</th>
                <th>İstifadəçi</th>
                <th>Modul</th>
                <th>Əməliyyat</th>
                <th>Açıqlama</th>
                <th>IP</th>
                <th>Baxış</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ optional($log->created_at)->format('d.m.Y H:i') }}</td>
                <td>{{ $log->admin_name ?: '-' }}</td>
                <td>{{ $log->module }}</td>
                <td><span class="badge badge-gray">{{ $log->action }}</span></td>
                <td class="log-description" title="{{ $log->description }}">{{ $log->description ?: '-' }}</td>
                <td>{{ $log->ip_address ?: '-' }}</td>
                <td>
                    <div class="action-list">
                        <button
                            class="btn btn-light action-icon"
                            type="button"
                            title="Detallı baxış"
                            aria-label="Detallı baxış"
                            data-log-detail
                            data-date="{{ optional($log->created_at)->format('d.m.Y H:i:s') ?: '-' }}"
                            data-user="{{ $log->admin_name ?: '-' }}"
                            data-module="{{ $log->module ?: '-' }}"
                            data-action="{{ $log->action ?: '-' }}"
                            data-object-type="{{ $log->object_type ?: '-' }}"
                            data-object-id="{{ $log->object_id ?: '-' }}"
                            data-ip="{{ $log->ip_address ?: '-' }}"
                            data-user-agent="{{ $log->user_agent ?: '-' }}"
                            data-description="{{ $log->description ?: '-' }}"
                        >
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Log yoxdur.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $logs->links() }}</div>
</div>

<div class="log-detail-backdrop" data-log-detail-modal aria-hidden="true">
    <div class="log-detail-panel" role="dialog" aria-modal="true" aria-labelledby="log-detail-title">
        <div class="log-detail-head">
            <h2 id="log-detail-title">Log detalları</h2>
            <button class="log-detail-close" type="button" data-log-detail-close aria-label="Bağla"><i class="ti ti-x"></i></button>
        </div>
        <div class="log-detail-grid">
            <div class="log-detail-item"><span>Tarix</span><strong data-log-detail-value="date">-</strong></div>
            <div class="log-detail-item"><span>İstifadəçi</span><strong data-log-detail-value="user">-</strong></div>
            <div class="log-detail-item"><span>Modul</span><strong data-log-detail-value="module">-</strong></div>
            <div class="log-detail-item"><span>Əməliyyat</span><strong data-log-detail-value="action">-</strong></div>
            <div class="log-detail-item"><span>Obyekt</span><strong data-log-detail-value="objectType">-</strong></div>
            <div class="log-detail-item"><span>Obyekt ID</span><strong data-log-detail-value="objectId">-</strong></div>
            <div class="log-detail-item"><span>IP</span><strong data-log-detail-value="ip">-</strong></div>
            <div class="log-detail-item"><span>Browser</span><strong data-log-detail-value="userAgent">-</strong></div>
            <div class="log-detail-item full">
                <span>Açıqlama</span>
                <p class="log-detail-description" data-log-detail-value="description">-</p>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const form = document.querySelector('[data-log-filter-form]');
        if (form) {
            let timer;
            const submit = () => form.requestSubmit ? form.requestSubmit() : form.submit();

            form.querySelectorAll('[data-log-filter-control]').forEach((control) => {
                control.addEventListener('change', submit);
            });

            const search = form.querySelector('[data-log-search]');
            const initialSearch = search?.value || '';
            search?.addEventListener('input', () => {
                clearTimeout(timer);
                const value = search.value.trim();
                if (value.length === 1) return;
                if (value.length === 0 && initialSearch.length === 0) return;
                timer = setTimeout(submit, 450);
            });
        }

        const modal = document.querySelector('[data-log-detail-modal]');
        const closeButton = document.querySelector('[data-log-detail-close]');
        const values = modal ? Object.fromEntries([...modal.querySelectorAll('[data-log-detail-value]')].map((el) => [el.dataset.logDetailValue, el])) : {};

        const setModalOpen = (open) => {
            if (!modal) return;
            modal.classList.toggle('is-open', open);
            modal.setAttribute('aria-hidden', open ? 'false' : 'true');
            document.body.classList.toggle('log-detail-open', open);
        };

        document.querySelectorAll('[data-log-detail]').forEach((button) => {
            button.addEventListener('click', () => {
                Object.entries(values).forEach(([key, element]) => {
                    element.textContent = button.dataset[key] || '-';
                });
                setModalOpen(true);
            });
        });

        closeButton?.addEventListener('click', () => setModalOpen(false));
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) setModalOpen(false);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                setModalOpen(false);
            }
        });
    })();
</script>
@endsection
