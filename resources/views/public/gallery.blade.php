@extends('layouts.public')

@php
    $ui = \App\Support\PublicUiText::all($lang);
@endphp

@section('title', $ui['gallery'] . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))

@push('styles')
<style>
.gallery-page{padding:46px 0 60px;background:#f4f7fb}.gallery-head{background:linear-gradient(135deg,#071728,#0b2c49);color:#fff;border-radius:28px;padding:34px 36px;margin-bottom:24px;box-shadow:0 22px 70px rgba(7,23,40,.13)}.gallery-head h1{margin:0;font-size:38px;line-height:1.18;font-weight:900}.gallery-head p{margin:10px 0 0;color:#d8e4ef;font-weight:750}.gallery-grid{display:grid;grid-template-columns:repeat(var(--gallery-cols,4),minmax(0,1fr));gap:18px}.gallery-card{position:relative;border:0;border-radius:22px;overflow:hidden;background:#fff;box-shadow:0 18px 55px rgba(7,23,40,.08);padding:0;cursor:pointer;animation:gIn .35s ease both}@keyframes gIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}.gallery-card img{width:100%;height:230px;object-fit:cover;transition:.25s ease}.gallery-card:hover img{transform:scale(1.06)}.gallery-overlay{position:absolute;inset:0;background:linear-gradient(180deg,transparent 35%,rgba(7,23,40,.84));display:flex;align-items:flex-end;padding:18px;color:#fff;opacity:.95}.gallery-overlay strong{display:block;font-size:17px;font-weight:900;line-height:1.3}.gallery-overlay span{display:block;margin-top:4px;font-size:13px;color:#d8e4ef;font-weight:750}.gallery-eye{position:absolute;top:14px;right:14px;width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.92);color:#071728;display:grid;place-items:center;font-size:17px;transform:scale(.9);opacity:0;transition:.2s ease}.gallery-card:hover .gallery-eye{opacity:1;transform:scale(1)}.gallery-empty{background:#fff;border:1px dashed #c7d5e5;border-radius:22px;padding:38px;text-align:center;color:#64748b;font-weight:850}.gallery-modal{position:fixed;inset:0;z-index:99999;background:rgba(7,23,40,.76);display:none;align-items:center;justify-content:center;padding:24px;backdrop-filter:blur(8px)}.gallery-modal.open{display:flex}.gallery-modal-box{width:min(980px,100%);background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 30px 110px rgba(0,0,0,.35);animation:mIn .22s ease both}@keyframes mIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}.gallery-modal-img{width:100%;max-height:70vh;object-fit:contain;background:#071728}.gallery-modal-info{padding:18px 22px;display:flex;justify-content:space-between;gap:16px;align-items:center}.gallery-modal-info h3{margin:0;font-size:20px;font-weight:900}.gallery-modal-info p{margin:4px 0 0;color:#64748b;font-weight:750}.modal-close{width:44px;height:44px;border:0;border-radius:50%;background:#ff8a1c;color:#fff;font-size:18px;display:grid;place-items:center}@media(max-width:1100px){.gallery-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:760px){.gallery-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.gallery-head h1{font-size:30px}}@media(max-width:520px){.gallery-grid{grid-template-columns:1fr}.gallery-card img{height:250px}}
</style>
@endpush

@section('content')
@include('public.partials.composition')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){const modal=document.getElementById('galleryModal'),modalImage=document.getElementById('modalImage'),modalTitle=document.getElementById('modalTitle'),modalDescription=document.getElementById('modalDescription'),modalClose=document.getElementById('modalClose');if(!modal)return;document.querySelectorAll('.gallery-card').forEach(card=>card.addEventListener('click',function(){modalImage.src=card.getAttribute('data-image')||'';modalTitle.textContent=card.getAttribute('data-title')||'';modalDescription.textContent=card.getAttribute('data-description')||'';modal.classList.add('open');document.body.style.overflow='hidden';}));function closeModal(){modal.classList.remove('open');modalImage.src='';document.body.style.overflow='';}modalClose.addEventListener('click',closeModal);modal.addEventListener('click',e=>{if(e.target===modal)closeModal();});document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});});
</script>
@endpush
