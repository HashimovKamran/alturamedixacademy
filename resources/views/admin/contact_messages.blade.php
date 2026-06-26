@extends('layouts.admin')
@section('title', 'Əlaqə mesajları')
@section('page_title', 'Əlaqə mesajları')
@section('content')
@if(session('status'))<div class="alert alert-ok">{{ session('status') }}</div>@endif
<div class="grid grid-4">
    <div class="card"><h2>{{ $stats['total'] }}</h2><div class="muted">Ümumi</div></div>
    <div class="card"><h2>{{ $stats['unread'] }}</h2><div class="muted">Oxunmamış</div></div>
    <div class="card"><h2>{{ $stats['read'] }}</h2><div class="muted">Oxunmuş</div></div>
    <div class="card"><h2>{{ $stats['today'] }}</h2><div class="muted">Bugün</div></div>
</div>
<div class="card">
    <h2>Filter</h2>
    <form method="get" class="grid grid-3">
        <div style="grid-column:span 2"><label>Axtarış</label><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ad, email, mövzu, mesaj..."></div>
        <div><label>Status</label><select name="read"><option value="">Hamısı</option><option value="0" @selected(($filters['read'] ?? '') === '0')>Oxunmamış</option><option value="1" @selected(($filters['read'] ?? '') === '1')>Oxunmuş</option></select></div>
        <div><button class="btn" type="submit">Göstər</button></div>
    </form>
</div>
<div class="card">
    @forelse($messages as $message)
        <div style="border-bottom:1px solid #edf1f6;padding:16px 0">
            <div style="display:flex;justify-content:space-between;gap:14px;align-items:flex-start;flex-wrap:wrap">
                <div><strong>{{ $message->full_name }}</strong> <span class="muted">{{ $message->email }} {{ $message->phone }}</span><div class="muted">{{ optional($message->created_at)->format('d.m.Y H:i') }} · {{ $message->ip_address }}</div></div>
                <span class="badge {{ $message->is_read ? 'badge-gray' : 'badge-orange' }}">{{ $message->is_read ? 'Oxunub' : 'Yeni' }}</span>
            </div>
            <p><strong>{{ $message->subject }}</strong></p>
            <p style="white-space:pre-wrap">{{ $message->message }}</p>
            <div class="action-list">
                @unless($message->is_read)
                    <form method="post" action="{{ route('admin.contact.read', $message) }}">
                        @csrf
                        <button class="btn btn-light action-icon" type="submit" title="Oxundu et" aria-label="Oxundu et"><i class="ti ti-mail-check"></i></button>
                    </form>
                @endunless
                <form method="post" action="{{ route('admin.contact.destroy', $message) }}" onsubmit="return confirm('Silinsin?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger action-icon" type="submit" title="Sil" aria-label="Sil"><i class="ti ti-trash"></i></button>
                </form>
            </div>
        </div>
    @empty
        <p>Mesaj yoxdur.</p>
    @endforelse
    <div style="margin-top:16px">{{ $messages->links() }}</div>
</div>
@endsection
