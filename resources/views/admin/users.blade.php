@extends('layouts.admin')
@section('title', 'İstifadəçilər')
@section('page_title', 'İstifadəçilər')
@section('content')
@if(session('status'))<div class="alert alert-ok">{{ session('status') }}</div>@endif
<div class="grid grid-4">
    <div class="card"><h2>{{ $stats['total'] }}</h2><div class="muted">Ümumi</div></div>
    <div class="card"><h2>{{ $stats['active'] }}</h2><div class="muted">Aktiv</div></div>
    <div class="card"><h2>{{ $stats['blocked'] }}</h2><div class="muted">Bloklu</div></div>
    <div class="card"><h2>{{ $stats['google'] }}</h2><div class="muted">Google login</div></div>
</div>
<div class="card">
    <h2>Filter</h2>
    <form method="get" class="grid grid-4">
        <div style="grid-column:span 2"><label>Axtarış</label><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ad, email, telefon..."></div>
        <div><label>Status</label><select name="active"><option value="">Hamısı</option><option value="1" @selected(($filters['active'] ?? '') === '1')>Aktiv</option><option value="0" @selected(($filters['active'] ?? '') === '0')>Bloklu</option></select></div>
        <div><label>Bildiriş</label><select name="notify"><option value="">Hamısı</option><option value="1" @selected(($filters['notify'] ?? '') === '1')>Aktiv</option><option value="0" @selected(($filters['notify'] ?? '') === '0')>Passiv</option></select></div>
        <div><button class="btn" type="submit">Göstər</button></div>
    </form>
</div>
<div class="card">
    <table>
        <thead><tr><th>Ad</th><th>Email</th><th>Telefon</th><th>Google</th><th>Bildiriş</th><th>Status</th><th>Son giriş</th><th>Tarix</th><th>Əməliyyat</th></tr></thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->full_name }}</td><td>{{ $user->email }}</td><td>{{ $user->phone }}</td><td>{{ $user->google_id ? 'Var' : '-' }}</td>
                <td>{{ $user->email_notify ? 'Aktiv' : 'Passiv' }}</td><td>{{ $user->is_active ? 'Aktiv' : 'Bloklu' }}</td>
                <td>{{ optional($user->last_login_at)->format('d.m.Y H:i') ?: '-' }}</td><td>{{ optional($user->created_at)->format('d.m.Y H:i') }}</td>
                <td>
                    <div class="action-list">
                        <form method="post" action="{{ route('admin.users.toggle', $user) }}">
                            @csrf
                            <input type="hidden" name="field" value="email_notify">
                            <button class="btn btn-light action-icon" type="submit" title="Email bildirişini dəyiş" aria-label="Email bildirişini dəyiş"><i class="ti ti-mail-cog"></i></button>
                        </form>
                        <form method="post" action="{{ route('admin.users.toggle', $user) }}">
                            @csrf
                            <input type="hidden" name="field" value="is_active">
                            <button class="btn btn-light action-icon" type="submit" title="Statusu dəyiş" aria-label="Statusu dəyiş"><i class="ti ti-user-check"></i></button>
                        </form>
                        <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Silinsin?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger action-icon" type="submit" title="Sil" aria-label="Sil"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">İstifadəçi yoxdur.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $users->links() }}</div>
</div>
@endsection
