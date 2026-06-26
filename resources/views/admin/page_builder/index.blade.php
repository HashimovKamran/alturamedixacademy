@php
    abort(404);
@endphp

@extends('layouts.admin')
@section('title', 'Səhifə redaktoru')
@section('page_title', 'Səhifə redaktoru')

@push('styles')
<style>
    .pb-top{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:18px}
    .pb-tabs{display:flex;gap:8px;flex-wrap:wrap}
    .pb-tabs a{border:1px solid var(--admin-line);background:#fff;padding:8px 11px;border-radius:999px;font-size:12px;font-weight:900}
    .pb-tabs a.active{background:var(--admin-accent);color:#111;border-color:var(--admin-accent)}
    .pb-shell{display:grid;grid-template-columns:370px minmax(0,1fr);gap:20px;align-items:start}
    .pb-form-grid{display:grid;gap:14px}
    .pb-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .pb-settings{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .pb-list{display:grid;gap:12px}
    .pb-item{background:#fff;border:1px solid var(--admin-line-2);border-radius:16px;padding:14px;display:grid;grid-template-columns:40px minmax(0,1fr) auto;gap:12px;align-items:center;box-shadow:0 12px 32px rgba(61,125,131,.045)}
    .pb-item.dragging{opacity:.45}
    .pb-handle{width:40px;height:40px;border-radius:12px;background:transparent;color:#6f7d82;display:grid;place-items:center;cursor:grab}
    .pb-handle i{font-size:18px;line-height:1}
    .pb-item h3{margin:0;color:#111;font-size:15px;font-weight:900}
    .pb-item p{margin:5px 0 0;color:var(--admin-muted);font-size:12px;font-weight:720}
    .pb-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .pb-actions .btn{padding:9px 11px;font-size:12px}
    .pb-preview{width:82px;height:56px;border-radius:10px;object-fit:cover;background:var(--admin-soft);border:1px solid var(--admin-line);margin-top:8px}
    .pb-empty{border:1px dashed #b9dcd7;border-radius:16px;padding:30px;text-align:center;color:var(--admin-muted);font-weight:850;background:var(--admin-mint-soft)}
    @media(max-width:1100px){.pb-shell{grid-template-columns:1fr}.pb-row,.pb-settings{grid-template-columns:1fr}.pb-top{display:block}}
</style>
@endpush

@section('content')
@if(session('status'))
    <div class="alert alert-ok">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="pb-top">
    <div></div>
    <a class="btn btn-light" href="{{ url($pageKey === 'index' ? '/' : '/page?key=' . urlencode($pageKey)) }}" target="_blank"><i class="ti ti-external-link"></i> Saytda bax</a>
</div>

<div class="pb-shell">
    <section class="card">
        <h2>{{ $edit ? 'Bloku redaktə et' : 'Yeni blok' }}</h2>
        <form method="post" action="{{ route('admin.page-builder.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $edit?->id }}">
            <input type="hidden" name="lang_code" value="{{ $selectedLanguage }}">
            <div class="pb-form-grid">
                <div class="form-row">
                    <label>Səhifə</label>
                    <select name="page_key">
                        @foreach($pages as $key => $title)
                            <option value="{{ $key }}" @selected($pageKey === $key)>{{ $title }} ({{ $key }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label>Blok tipi</label>
                    <select name="block_type">
                        @foreach($blockTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('block_type', $edit->block_type ?? 'text') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label>Başlıq</label>
                    <input type="text" name="title" value="{{ old('title', $edit->title ?? '') }}" required>
                </div>

                <div class="form-row">
                    <label>Alt başlıq / Kicker</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $edit->subtitle ?? '') }}">
                </div>

                <div class="form-row">
                    <label>Mətn</label>
                    <textarea name="body" rows="8">{{ old('body', $edit->body ?? '') }}</textarea>
                    <div class="help">Kart və FAQ bloklarında hər sətri belə yaz: Başlıq | Mətn</div>
                </div>

                <div class="pb-row">
                    <div class="form-row">
                        <label>Düymə mətni</label>
                        <input type="text" name="button_text" value="{{ old('button_text', $edit->button_text ?? '') }}">
                    </div>
                    <div class="form-row">
                        <label>Düymə linki</label>
                        <input type="text" name="button_url" value="{{ old('button_url', $edit->button_url ?? '#') }}">
                    </div>
                </div>

                <div class="pb-row">
                    <div class="form-row">
                        <label>Şəkil</label>
                        <input type="file" name="image_path">
                        @if($edit?->image_path)<img class="pb-preview" src="{{ asset(ltrim($edit->image_path, '/')) }}" alt="">@endif
                    </div>
                    <div class="form-row">
                        <label>Sıra</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $edit->sort_order ?? 0) }}">
                    </div>
                </div>

                <h2 style="margin-top:10px">Görünüş</h2>
                <div class="pb-settings">
                    <div class="form-row"><label>Fon rəngi</label><input type="text" name="bg_color" value="{{ $settings['bg_color'] }}"></div>
                    <div class="form-row"><label>Mətn rəngi</label><input type="text" name="text_color" value="{{ $settings['text_color'] }}"></div>
                    <div class="form-row"><label>Accent rəngi</label><input type="text" name="accent_color" value="{{ $settings['accent_color'] }}"></div>
                    <div class="form-row"><label>Radius</label><input type="number" name="radius" min="0" max="60" value="{{ $settings['radius'] }}"></div>
                    <div class="form-row"><label>Padding Y</label><input type="number" name="padding_y" min="12" max="140" value="{{ $settings['padding_y'] }}"></div>
                    <div class="form-row">
                        <label>Layout</label>
                        <select name="layout">
                            @foreach(['card' => 'Card', 'wide' => 'Wide', 'centered' => 'Centered'] as $key => $label)
                                <option value="{{ $key }}" @selected($settings['layout'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Align</label>
                        <select name="align">
                            @foreach(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'] as $key => $label)
                                <option value="{{ $key }}" @selected($settings['align'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Shadow</label>
                        <select name="shadow">
                            @foreach(['none' => 'None', 'soft' => 'Soft', 'strong' => 'Strong'] as $key => $label)
                                <option value="{{ $key }}" @selected($settings['shadow'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Animasiya</label>
                        <select name="animation">
                            @foreach(['fade-up' => 'Fade up', 'zoom' => 'Zoom', 'slide-left' => 'Slide left', 'none' => 'None'] as $key => $label)
                                <option value="{{ $key }}" @selected($settings['animation'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Şəkil mövqeyi</label>
                        <select name="image_position">
                            @foreach(['right' => 'Right', 'left' => 'Left'] as $key => $label)
                                <option value="{{ $key }}" @selected($settings['image_position'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Max width</label>
                        <select name="max_width">
                            @foreach(['full' => 'Full', 'boxed' => 'Boxed', 'narrow' => 'Narrow'] as $key => $label)
                                <option value="{{ $key }}" @selected($settings['max_width'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row"><label>Custom class</label><input type="text" name="custom_class" value="{{ $settings['custom_class'] }}"></div>
                </div>

                <label class="check" style="display:flex;align-items:center;gap:10px">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $edit->is_active ?? true)) style="width:auto"> Aktiv
                </label>

                <button class="btn btn-primary" type="submit"><i class="ti ti-device-floppy"></i> Yadda saxla</button>
                @if($edit)
                    <a class="btn btn-light" href="{{ route('admin.page-builder.index', ['lang_code' => $selectedLanguage, 'page' => $pageKey]) }}">Yeni blok</a>
                @endif
            </div>
        </form>
    </section>

    <section class="card">
        <h2>{{ $pages[$pageKey] ?? $pageKey }} blokları</h2>
        <div class="pb-tabs" style="margin-bottom:18px">
            @foreach($pages as $key => $title)
                <a class="{{ $pageKey === $key ? 'active' : '' }}" href="{{ route('admin.page-builder.index', ['lang_code' => $selectedLanguage, 'page' => $key]) }}">{{ $title }}</a>
            @endforeach
        </div>

        @if($blocks->isNotEmpty())
            <div class="pb-list" id="pbList">
                @foreach($blocks as $block)
                    <article class="pb-item" draggable="true" data-id="{{ $block->id }}">
                        <div class="pb-handle"><i class="ti ti-grip-vertical"></i></div>
                        <div>
                            <h3>{{ $block->title ?: $blockTypes[$block->block_type] ?? 'Blok' }}</h3>
                            <p>{{ $blockTypes[$block->block_type] ?? $block->block_type }} / #{{ $block->sort_order }} / {{ $block->is_active ? 'aktiv' : 'passiv' }}</p>
                        </div>
                        <div class="pb-actions">
                            <a class="btn btn-light" href="{{ route('admin.page-builder.index', ['lang_code' => $selectedLanguage, 'page' => $pageKey, 'edit' => $block->id]) }}"><i class="ti ti-pencil"></i></a>
                            <form method="post" action="{{ route('admin.page-builder.duplicate', $block) }}">
                                @csrf
                                <button class="btn btn-light" type="submit"><i class="ti ti-copy"></i></button>
                            </form>
                            <form method="post" action="{{ route('admin.page-builder.destroy', $block) }}" onsubmit="return confirm('Blok silinsin?')">
                                @csrf
                                @method('delete')
                                <button class="btn btn-danger" type="submit"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="pb-empty">Bu səhifə üçün hələ blok yoxdur.</div>
        @endif
    </section>
</div>

<script>
    const list = document.getElementById('pbList');
    if (list) {
        let dragged = null;
        list.querySelectorAll('.pb-item').forEach((item) => {
            item.addEventListener('dragstart', () => {
                dragged = item;
                item.classList.add('dragging');
            });
            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
                saveOrder();
            });
        });
        list.addEventListener('dragover', (event) => {
            event.preventDefault();
            const after = getDragAfterElement(list, event.clientY);
            if (!dragged) return;
            if (after == null) list.appendChild(dragged);
            else list.insertBefore(dragged, after);
        });
        function getDragAfterElement(container, y) {
            const items = [...container.querySelectorAll('.pb-item:not(.dragging)')];
            return items.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) return {offset, element: child};
                return closest;
            }, {offset: Number.NEGATIVE_INFINITY}).element;
        }
        async function saveOrder() {
            const order = [...list.querySelectorAll('.pb-item')].map((item) => item.dataset.id);
            const form = new FormData();
            form.append('_token', '{{ csrf_token() }}');
            form.append('lang_code', '{{ $selectedLanguage }}');
            form.append('page_key', '{{ $pageKey }}');
            form.append('order', JSON.stringify(order));
            await fetch('{{ route('admin.page-builder.sort') }}', {method: 'POST', body: form});
        }
    }
</script>
@endsection
