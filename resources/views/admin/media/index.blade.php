@extends('layouts.admin')
@section('title', 'Media kitabxanası')
@section('page_title', 'Media kitabxanası')

@push('styles')
<style>
    .media-page-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px}
    .media-page-head h2{margin:0;font-size:24px;line-height:1.15;font-weight:900;letter-spacing:-.025em;color:#111}
    .media-page-head p{margin:6px 0 0;color:var(--admin-muted);font-size:13px;font-weight:760}
    .media-page-head .btn-primary,.media-modal-actions .btn-primary{background:#ff7a1a;color:#fff;box-shadow:0 14px 26px rgba(255,122,26,.24)}
    .media-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .media-stat{background:#fff;border:1px solid var(--admin-line-2);border-radius:16px;padding:16px;box-shadow:0 12px 32px rgba(61,125,131,.05);display:flex;align-items:center;gap:12px;min-width:0}
    .media-stat i{display:grid;place-items:center;width:34px;height:34px;border-radius:12px;background:#f7fbfa;color:#6f7d82;font-size:18px;line-height:1;flex:0 0 auto}
    .media-stat strong{display:block;font-size:24px;font-weight:900;color:#111;letter-spacing:-.02em;line-height:1.1}
    .media-stat span{display:block;margin-top:3px;color:var(--admin-muted);font-size:12px;font-weight:800}
    .media-filter-card{padding:18px;margin-bottom:18px}
    .media-filter-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px}
    .media-filter-head h2{margin:0;font-size:18px}
    .filter-grid{display:grid;grid-template-columns:minmax(220px,1fr) 190px 170px auto;gap:12px;align-items:end}
    .filter-grid .btn{min-height:42px}
    .media-grid-card{padding:18px}
    .media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
    .media-card{background:#fff;border:1px solid var(--admin-line-2);border-radius:17px;overflow:hidden;box-shadow:0 12px 32px rgba(61,125,131,.045);display:flex;flex-direction:column;min-height:310px}
    .media-preview{height:154px;background:#f2faf8;display:grid;place-items:center;overflow:hidden;border-bottom:1px solid var(--admin-line-2)}
    .media-preview img{width:100%;height:100%;object-fit:cover}
    .media-preview i{font-size:38px;color:#6f7d82}
    .media-body{padding:14px;display:flex;flex:1;flex-direction:column;min-width:0}
    .media-name{font-weight:900;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .media-meta{margin-top:8px;color:var(--admin-muted);font-size:12px;font-weight:760;display:grid;gap:4px}
    .media-meta span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .media-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-top:auto;padding-top:13px}
    .media-actions form{display:inline-flex;margin:0}
    .media-actions .action-icon.btn{width:36px;height:36px;padding:0;border-radius:11px}
    .media-actions .action-icon.btn i{font-size:18px}
    .media-actions .action-icon.is-copied{background:#eaf9ef;border-color:#c7efd3;color:#1f8f4d}
    .empty-media{border:1px dashed #b9dcd7;border-radius:18px;padding:34px;text-align:center;color:var(--admin-muted);font-weight:850;background:#f8fdfb}
    .media-toast-wrap{position:fixed;top:18px;left:50%;z-index:180;display:grid;justify-items:center;gap:10px;width:max-content;max-width:calc(100vw - 32px);transform:translateX(-50%);pointer-events:none}
    .media-toast{display:flex;align-items:center;justify-content:center;gap:10px;width:max-content;max-width:calc(100vw - 32px);padding:13px 16px;border-radius:15px;background:#fff;border:1px solid var(--admin-line);box-shadow:0 22px 56px rgba(15,23,42,.18);font-size:13px;font-weight:850;color:#111;line-height:1.45;text-align:center;animation:mediaToastDrop .28s ease-out both;pointer-events:auto}
    .media-toast i{width:24px;height:24px;border-radius:999px;display:grid;place-items:center;flex:0 0 auto;font-size:16px;background:#eaf9ef;color:#1f8f4d}
    .media-toast-error i{background:#fff1f2;color:#c94c5a}
    .media-toast.is-hiding{animation:mediaToastHide .2s ease-in both}
    body.media-modal-open{overflow:hidden}
    .media-modal{position:fixed;inset:0;z-index:130;display:none;background:rgba(15,23,42,.34);backdrop-filter:blur(8px);padding:24px;overflow:auto;overscroll-behavior:contain}
    .media-modal.is-open{display:grid;place-items:center}
    .media-modal-panel{width:min(760px,calc(100vw - 32px));background:#fff;border:1px solid var(--admin-line);border-radius:20px;padding:28px;box-shadow:0 28px 90px rgba(17,24,39,.18)}
    .media-modal-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:22px}
    .media-modal-head h2{margin:0;font-size:22px;font-weight:900;letter-spacing:-.02em;color:#111}
    .media-modal-close{width:40px;height:40px;border-radius:13px;border:1px solid var(--admin-line);display:grid;place-items:center;background:#fff;color:#111;cursor:pointer;font-size:18px}
    .media-upload-form{display:grid;gap:18px}
    .media-upload-grid{display:grid;grid-template-columns:1fr;gap:18px}
    .media-upload-form .form-row{display:flex;flex-direction:column;min-width:0}
    .media-upload-form label{margin-bottom:8px}
    .media-upload-form select{height:52px;border-radius:13px}
    .media-file-input{position:absolute;width:1px!important;height:1px!important;opacity:0;pointer-events:none}
    .media-file-drop{display:flex!important;align-items:center;gap:14px;margin:0!important;padding:16px!important;border:1px dashed #c6dfdc;border-radius:16px;background:#f8fdfb;cursor:pointer;transition:border-color .16s ease,background-color .16s ease,box-shadow .16s ease}
    .media-file-drop:hover{border-color:#ffb36f;background:#fffaf5;box-shadow:0 10px 24px rgba(255,122,26,.08)}
    .media-file-icon{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;background:#fff3e8;color:#ff7a1a;font-size:24px;flex:0 0 auto}
    .media-file-copy{display:grid;gap:4px;min-width:0}
    .media-file-copy strong{font-size:14px;font-weight:900;color:#111}
    .media-file-copy small{font-size:12px;font-weight:760;color:var(--admin-muted);white-space:normal}
    .media-modal-actions{display:flex;align-items:center;gap:10px;margin-top:4px;padding-top:18px;border-top:1px solid var(--admin-line-2)}
    @keyframes mediaToastDrop{from{opacity:0;transform:translateY(-18px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
    @keyframes mediaToastHide{to{opacity:0;transform:translateY(-14px) scale(.98)}}
    @media(max-width:1200px){.media-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-grid{grid-template-columns:1fr 180px 160px auto}}
    @media(max-width:760px){.media-page-head{align-items:stretch;flex-direction:column}.media-page-head .btn{width:100%}.media-stats,.filter-grid{grid-template-columns:1fr}.media-modal{padding:14px}.media-modal-panel{padding:20px}.media-modal-actions{flex-direction:column;align-items:stretch}.media-modal-actions .btn{width:100%}}
</style>
@endpush

@section('content')
@php
    $shouldOpenUploadModal = $errors->has('media_files');
@endphp
@if(session('status') || $errors->any())
    <div class="media-toast-wrap" data-media-toast-wrap>
        @if(session('status'))
            <div class="media-toast" role="status" data-media-toast>
                <i class="ti ti-check"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="media-toast media-toast-error" role="alert" data-media-toast>
                <i class="ti ti-alert-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
    </div>
@endif

<div class="media-page-head">
    <div>
        <h2>Media faylları</h2>
        <p>Şəkil, sənəd və digər yüklənən faylları buradan idarə edin.</p>
    </div>
    <button class="btn btn-primary" type="button" data-media-upload-open><i class="ti ti-cloud-upload"></i> Yeni fayl</button>
</div>

<section class="media-stats">
    <div class="media-stat"><i class="ti ti-folder-open"></i><div><strong>{{ $stats['totalFiles'] }}</strong><span>Ümumi fayl</span></div></div>
    <div class="media-stat"><i class="ti ti-photo"></i><div><strong>{{ $stats['totalImages'] }}</strong><span>Şəkil</span></div></div>
    <div class="media-stat"><i class="ti ti-file-text"></i><div><strong>{{ $stats['totalDocs'] }}</strong><span>Sənəd</span></div></div>
    <div class="media-stat"><i class="ti ti-device-desktop"></i><div><strong>{{ media_size($stats['totalSize']) }}</strong><span>Yaddaş həcmi</span></div></div>
</section>

<section class="card media-filter-card">
    <div class="media-filter-head">
        <h2>Fayllar</h2>
        <span class="muted">{{ count($files) }} nəticə</span>
    </div>
    <form class="filter-grid" method="get" action="{{ route('admin.media.index') }}">
        <div>
            <label>Axtarış</label>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Fayl adı və ya yol">
        </div>
        <div>
            <label>Qovluq</label>
            <select name="folder">
                <option value="">Hamısı</option>
                @foreach($folders as $key => $label)
                    <option value="{{ $key }}" @selected($filters['folder'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Tip</label>
            <select name="type">
                <option value="">Hamısı</option>
                <option value="image" @selected($filters['type'] === 'image')>Şəkillər</option>
                <option value="document" @selected($filters['type'] === 'document')>Sənədlər</option>
            </select>
        </div>
        <button class="btn btn-light" type="submit"><i class="ti ti-search"></i> Göstər</button>
    </form>
</section>

<section class="card media-grid-card">
    @if($files)
        <div class="media-grid">
            @foreach($files as $file)
                <article class="media-card">
                    <div class="media-preview">
                        @if($file['is_image'])
                            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}">
                        @else
                            <i class="{{ media_icon($file['ext']) }}"></i>
                        @endif
                    </div>
                    <div class="media-body">
                        <div class="media-name" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
                        <div class="media-meta">
                            <span title="{{ $file['folder'] }}">{{ $file['folder'] }}</span>
                            <span>{{ strtoupper($file['ext']) }} / {{ media_size($file['size']) }}</span>
                            <span>{{ date('d.m.Y H:i', $file['modified']) }}</span>
                        </div>
                        <div class="media-actions">
                            <a class="btn btn-light action-icon" href="{{ $file['url'] }}" target="_blank" title="Aç" aria-label="Aç"><i class="ti ti-external-link"></i></a>
                            <button class="btn btn-light action-icon" type="button" data-copy-path="{{ $file['relative'] }}" title="Yolu kopyala" aria-label="Yolu kopyala"><i class="ti ti-copy"></i></button>
                            <form method="post" action="{{ route('admin.media.destroy') }}" onsubmit="return confirm('Fayl silinsin?')">
                                @csrf
                                @method('delete')
                                <input type="hidden" name="file_path" value="{{ $file['relative'] }}">
                                <button class="btn btn-danger action-icon" type="submit" title="Sil" aria-label="Sil"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="empty-media">Uyğun fayl tapılmadı.</div>
    @endif
</section>

<div class="media-modal {{ $shouldOpenUploadModal ? 'is-open' : '' }}" data-media-upload-modal>
    <div class="media-modal-panel">
        <div class="media-modal-head">
            <h2>Yeni fayl yüklə</h2>
            <button class="media-modal-close" type="button" data-media-upload-close aria-label="Bağla"><i class="ti ti-x"></i></button>
        </div>
        <form class="media-upload-form" method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="media-upload-grid">
                <div class="form-row">
                    <label>Qovluq</label>
                    <select name="folder">
                        @foreach($folders as $key => $label)
                            <option value="{{ $key }}" @selected(old('folder', 'media') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <label>Fayllar</label>
                    <input id="mediaFilesInput" class="media-file-input" type="file" name="media_files[]" multiple data-media-file-input>
                    <label class="media-file-drop" for="mediaFilesInput">
                        <span class="media-file-icon"><i class="ti ti-photo-up"></i></span>
                        <span class="media-file-copy">
                            <strong>Fayl seç</strong>
                            <small data-media-file-name>JPG, PNG, WEBP, GIF, SVG, PDF, DOCX, XLSX, PPTX və ZIP. Maksimum 25 MB.</small>
                        </span>
                    </label>
                </div>
            </div>
            <div class="media-modal-actions">
                <button class="btn btn-primary" type="submit"><i class="ti ti-cloud-upload"></i> Yüklə</button>
                <button class="btn btn-light" type="button" data-media-upload-close>Ləğv et</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.querySelector('[data-media-upload-modal]');
    const openButton = document.querySelector('[data-media-upload-open]');
    const closeButtons = document.querySelectorAll('[data-media-upload-close]');
    let toastWrap = document.querySelector('[data-media-toast-wrap]');

    if (modal?.classList.contains('is-open')) {
        document.body.classList.add('media-modal-open');
    }

    function setModalOpen(open) {
        modal?.classList.toggle('is-open', open);
        document.body.classList.toggle('media-modal-open', open);
    }

    openButton?.addEventListener('click', () => setModalOpen(true));
    closeButtons.forEach((button) => button.addEventListener('click', () => setModalOpen(false)));
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) setModalOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
            setModalOpen(false);
        }
    });

    document.querySelectorAll('[data-media-toast]').forEach((toast) => {
        window.setTimeout(() => dismissToast(toast), 4200);
    });

    document.querySelectorAll('[data-media-file-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const name = input.closest('.form-row')?.querySelector('[data-media-file-name]');
            if (!name) return;
            const files = Array.from(input.files || []);
            if (files.length === 0) {
                name.textContent = 'JPG, PNG, WEBP, GIF, SVG, PDF, DOCX, XLSX, PPTX və ZIP. Maksimum 25 MB.';
                return;
            }
            name.textContent = files.length === 1 ? files[0].name : `${files.length} fayl seçildi`;
        });
    });

    document.querySelectorAll('[data-copy-path]').forEach((button) => {
        button.addEventListener('click', async () => {
            const path = button.getAttribute('data-copy-path');
            try {
                await navigator.clipboard.writeText(path);
                button.classList.add('is-copied');
                button.innerHTML = '<i class="ti ti-check"></i>';
                button.setAttribute('title', 'Kopyalandı');
                button.setAttribute('aria-label', 'Kopyalandı');
                showToast('Fayl yolu kopyalandı.');
                window.setTimeout(() => {
                    button.classList.remove('is-copied');
                    button.innerHTML = '<i class="ti ti-copy"></i>';
                    button.setAttribute('title', 'Yolu kopyala');
                    button.setAttribute('aria-label', 'Yolu kopyala');
                }, 1200);
            } catch (error) {
                window.prompt('Fayl yolu', path);
            }
        });
    });

    function showToast(message) {
        const wrap = toastWrap || createToastWrap();
        const toast = document.createElement('div');
        toast.className = 'media-toast';
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<i class="ti ti-check"></i><span></span>';
        toast.querySelector('span').textContent = message;
        wrap.appendChild(toast);
        window.setTimeout(() => dismissToast(toast), 2200);
    }

    function createToastWrap() {
        const wrap = document.createElement('div');
        wrap.className = 'media-toast-wrap';
        wrap.setAttribute('data-media-toast-wrap', '');
        document.body.appendChild(wrap);
        toastWrap = wrap;
        return wrap;
    }

    function dismissToast(toast) {
        if (!toast || toast.classList.contains('is-hiding')) return;
        toast.classList.add('is-hiding');
        window.setTimeout(() => toast.remove(), 220);
    }
})();
</script>
@endsection

@php
    function media_size(int $bytes): string {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
        }
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    function media_icon(string $ext): string {
        return match ($ext) {
            'pdf' => 'ti ti-file-type-pdf',
            'doc', 'docx' => 'ti ti-file-type-doc',
            'xls', 'xlsx' => 'ti ti-file-type-xls',
            'ppt', 'pptx' => 'ti ti-file-type-ppt',
            'zip' => 'ti ti-file-zip',
            default => 'ti ti-file',
        };
    }
@endphp
