@extends('layouts.public')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . ($settings['site_name'] ?? 'ALTURAMEDIX ACADEMY'))
@section('meta_description', $page->meta_description ?? '')
@section('robots', $page->robots ?? 'index,follow')
@if($page->meta_image) @section('meta_image', asset(ltrim($page->meta_image, '/'))) @endif

@php
    $image = trim((string) $page->image_path);
    $cleanBody = app(\App\Support\Cms\SafeHtml::class)->clean($page->body);
    $ui = \App\Support\PublicUiText::all($lang);
    $cleanMapEmbed = function (string $html, int $height): string {
        $html = trim($html);
        if ($html === '') return '';
        $height = max(240, min(700, $height));
        $src = $html;
        if (stripos($html, '<iframe') !== false && preg_match('/\ssrc\s*=\s*(["\'])(.*?)\1/i', $html, $match)) $src = $match[2];
        if (!preg_match('#^https://(?:www\.)?(?:google\.[a-z.]+|maps\.google\.[a-z.]+)/maps/#i', $src)) return '';
        return '<div class="aa-home-map-frame" style="height:' . $height . 'px"><iframe src="' . e($src) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>';
    };
    $contactMapHtml = ($page->page_key === 'contact' && (($settings['home_map_enabled'] ?? '0') === '1')) ? $cleanMapEmbed((string)($settings['home_map_embed'] ?? ''), (int)($settings['home_map_height'] ?? 360)) : '';
    $aboutText = function (string $key, string $fallback) use ($settings): string {
        $value = trim((string) ($settings[$key] ?? ''));
        return $value !== '' ? $value : $fallback;
    };
    $aboutSections = match ($lang) {
        'en' => [
            ['key' => 'who', 'icon' => 'fa-solid fa-users', 'title' => 'Who we are', 'text' => $aboutText('about_who_text', 'Alturamedix Academy is an academic platform for medical education, clinical knowledge sharing, and professional development.')],
            ['key' => 'mission', 'icon' => 'fa-solid fa-bullseye', 'title' => 'Our mission', 'text' => $aboutText('about_mission_text', 'Our mission is to make high-quality emergency and critical care knowledge more accessible to physicians, healthcare professionals, and students.')],
            ['key' => 'board', 'icon' => 'fa-solid fa-user-tie', 'title' => 'Management board', 'text' => $aboutText('about_board_text', 'The management board coordinates academic direction, training quality, and professional partnerships.')],
        ],
        'ru' => [
            ['key' => 'who', 'icon' => 'fa-solid fa-users', 'title' => 'Кто мы', 'text' => $aboutText('about_who_text', 'Alturamedix Academy - академическая платформа для медицинского образования, обмена клиническими знаниями и профессионального развития.')],
            ['key' => 'mission', 'icon' => 'fa-solid fa-bullseye', 'title' => 'Наша миссия', 'text' => $aboutText('about_mission_text', 'Наша миссия - сделать качественные знания в области неотложной и критической медицины более доступными для врачей, медицинских работников и студентов.')],
            ['key' => 'board', 'icon' => 'fa-solid fa-user-tie', 'title' => 'Руководство', 'text' => $aboutText('about_board_text', 'Руководство координирует академическое направление, качество обучения и профессиональные партнерства.')],
        ],
        'tr' => [
            ['key' => 'who', 'icon' => 'fa-solid fa-users', 'title' => 'Biz kimiz', 'text' => $aboutText('about_who_text', 'Alturamedix Academy tıp eğitimi, klinik bilgi paylaşımı ve profesyonel gelişim için oluşturulmuş akademik bir platformdur.')],
            ['key' => 'mission', 'icon' => 'fa-solid fa-bullseye', 'title' => 'Misyonumuz', 'text' => $aboutText('about_mission_text', 'Misyonumuz acil ve kritik tıp alanındaki kaliteli bilgileri hekimler, sağlık çalışanları ve öğrenciler için daha erişilebilir kılmaktır.')],
            ['key' => 'board', 'icon' => 'fa-solid fa-user-tie', 'title' => 'Yönetim ekibi', 'text' => $aboutText('about_board_text', 'Yönetim ekibi akademik yönü, eğitim kalitesini ve profesyonel iş birliklerini koordine eder.')],
        ],
        default => [
            ['key' => 'who', 'icon' => 'fa-solid fa-users', 'title' => 'Biz kimik', 'text' => $aboutText('about_who_text', 'Alturamedix Academy tibbi təhsil, klinik bilik paylaşımı və peşəkar inkişaf üçün yaradılmış akademik platformadır.')],
            ['key' => 'mission', 'icon' => 'fa-solid fa-bullseye', 'title' => 'Missiyamız', 'text' => $aboutText('about_mission_text', 'Məqsədimiz təcili və kritik tibb sahəsində keyfiyyətli bilikləri həkimlərə, tibb işçilərinə və tələbələrə daha əlçatan etməkdir.')],
            ['key' => 'board', 'icon' => 'fa-solid fa-user-tie', 'title' => 'İdarə heyəti', 'text' => $aboutText('about_board_text', 'İdarə heyəti akademik istiqaməti, təlim keyfiyyətini və peşəkar əməkdaşlıqları koordinasiya edir.')],
        ],
    };
    $aboutSectionKey = in_array((string) request()->query('section'), ['who', 'mission', 'board'], true)
        ? (string) request()->query('section')
        : 'who';
    $selectedAboutSection = $page->page_key === 'about'
        ? collect($aboutSections)->firstWhere('key', $aboutSectionKey)
        : null;
    $displayTitle = $selectedAboutSection['title'] ?? $page->title;
    $displaySubtitle = $selectedAboutSection ? $page->title : $page->subtitle;
    $displayBody = $selectedAboutSection
        ? '<p>' . e($selectedAboutSection['text']) . '</p>'
        : $cleanBody;
@endphp

@if($page->page_key === 'contact')
    @push('styles')
    <style>
        .contact-page{padding:46px 0 64px;background:radial-gradient(circle at 12% 0%,rgba(255,138,28,.08),transparent 26%),linear-gradient(180deg,#f7fbff 0%,#f3f6fa 100%)}
        .contact-grid{position:relative;overflow:hidden;display:grid;grid-template-columns:minmax(0,1fr) 430px;gap:34px;align-items:center;background:linear-gradient(135deg,#061727,#0b2d4b);border-radius:30px;padding:38px;box-shadow:0 26px 76px rgba(7,23,40,.16);animation:contactIn .35s ease both}
        .contact-grid::before{content:"";position:absolute;right:28%;top:-110px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.055)}
        .contact-grid::after{content:"";position:absolute;right:-110px;bottom:-170px;width:360px;height:360px;border-radius:50%;background:rgba(255,138,28,.14)}
        .contact-info,.contact-form{position:relative;z-index:2}
        @keyframes contactIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
        .contact-info{color:#fff;max-width:760px}
        .contact-info h1{margin:0;font-size:clamp(38px,4vw,56px);line-height:1.05;font-weight:950;letter-spacing:0}
        .contact-info .sub{margin:14px 0 22px;color:#d7e5f2;font-size:18px;font-weight:850}
        .contact-body{max-width:760px;font-size:17px;line-height:1.85;color:#e6f0f8;font-weight:720}
        .contact-body p{margin:0 0 14px}
        .contact-body p:last-child{margin-bottom:0}
        .contact-items{max-width:680px;margin-top:28px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .contact-item{min-width:0;display:flex;gap:12px;align-items:center;background:rgba(255,255,255,.075);border:1px solid rgba(255,255,255,.13);border-radius:18px;padding:14px}
        .contact-item:last-child{grid-column:1 / -1}
        .contact-item i{width:42px;height:42px;border-radius:14px;background:rgba(255,138,28,.14);color:#ff8a1c;display:grid;place-items:center;font-size:17px;flex:0 0 auto}
        .contact-item strong{display:block;color:#fff;font-weight:900}
        .contact-item span{display:block;color:#c6d5e2;font-size:13px;line-height:1.35;font-weight:780;overflow-wrap:anywhere}
        .contact-form{background:#fff;border:1px solid rgba(219,228,238,.9);border-radius:24px;box-shadow:0 24px 70px rgba(0,0,0,.18);padding:28px;color:#071728}
        .contact-form::before{content:"";position:absolute;left:28px;right:28px;top:0;height:4px;background:#ff8a1c;border-radius:0 0 999px 999px}
        .contact-form h2{margin:0 0 18px;font-size:28px;line-height:1.15;font-weight:950;letter-spacing:-.02em}
        .contact-form form{display:grid;gap:12px}
        .form-row{margin:0}
        .form-row label{display:block;margin-bottom:7px;font-size:13px;font-weight:900}
        .form-row input,.form-row textarea{width:100%;border:1px solid #dbe4ee;border-radius:15px;padding:0 14px;font-family:inherit;font-weight:700;outline:0;background:#fbfdff}
        .form-row input{height:50px}
        .form-row textarea{min-height:118px;padding-top:12px;resize:vertical}
        .form-row input:focus,.form-row textarea:focus{border-color:#ff8a1c;background:#fff;box-shadow:0 0 0 4px rgba(255,138,28,.12)}
        .contact-form button{height:54px;border-radius:15px;width:100%;font-size:16px}
        .alert{border-radius:14px;padding:13px 15px;margin-bottom:16px;font-weight:850}
        .alert-ok{background:#ecfff4;border:1px solid #baf7cf;color:#166534}
        .alert-error{background:#fff0f0;border:1px solid #ffcaca;color:#991b1b}
        .contact-map-container{margin-top:28px}
        .contact-page .aa-home-map-section{margin:0}
        .contact-page .aa-home-map-card{border-radius:28px;box-shadow:0 22px 68px rgba(7,23,40,.08)}
        .contact-page .aa-home-map-head{padding:24px 26px}
        .contact-page .aa-home-map-frame{border-radius:0 0 28px 28px}
        @media(max-width:1180px){.contact-items{grid-template-columns:1fr}.contact-item:last-child{grid-column:auto}}
        @media(max-width:900px){.contact-grid{grid-template-columns:1fr;padding:28px;border-radius:24px}.contact-info h1{font-size:34px}.contact-form{box-shadow:0 20px 52px rgba(0,0,0,.14)}}
        @media(max-width:640px){.contact-page{padding:24px 0 46px}.contact-grid{padding:22px}.contact-form{padding:22px;border-radius:20px}.contact-form::before{left:22px;right:22px}.contact-form h2{font-size:25px}.contact-map-container{margin-top:22px}}
    </style>
    @endpush
@else
    @push('styles')
    <style>
        .simple-page{padding:54px 0 72px;background:linear-gradient(180deg,#f4f7fb 0%,#fff 46%,#f4f7fb 100%)}
        .page-box{display:grid;grid-template-columns:minmax(0,1fr) 430px;min-height:500px;background:rgba(255,255,255,.96);border:1px solid #dbe4ee;border-radius:30px;box-shadow:0 24px 74px rgba(7,23,40,.08);overflow:hidden;animation:pageIn .35s ease both}.page-box.no-cover{grid-template-columns:1fr}
        @keyframes pageIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
        .page-content{padding:clamp(32px,4.2vw,62px);display:flex;flex-direction:column;justify-content:center}
        .page-content::before{content:"";width:58px;height:5px;border-radius:999px;background:#ff8a1c;margin-bottom:22px}
        .page-content h1{margin:0;font-size:clamp(36px,4vw,56px);line-height:1.05;font-weight:950;color:#071728;letter-spacing:0}
        .page-content .sub{margin:14px 0 26px;color:#607083;font-size:18px;font-weight:900}
        .body-text{max-width:820px;font-size:18px;line-height:1.85;color:#10263a;font-weight:760}
        .body-text p{margin:0 0 18px}
        .body-text p:last-child{margin-bottom:0}
        .about-heading-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:30px}
        .about-heading-card{display:flex;flex-direction:column;align-items:flex-start;gap:14px;min-height:178px;border:1px solid #dbe4ee;border-radius:18px;background:linear-gradient(180deg,#fff,#f8fbff);padding:20px;box-shadow:0 14px 36px rgba(7,23,40,.055)}
        .about-heading-title{display:flex;align-items:center;gap:13px}
        .about-heading-card i{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:rgba(255,138,28,.12);color:#ff8a1c;font-size:17px;flex:0 0 auto}
        .about-heading-card h2{margin:0;color:#071728;font-size:18px;line-height:1.22;font-weight:950;letter-spacing:0}
        .about-heading-card p{margin:0;color:#607083;font-size:14px;line-height:1.65;font-weight:760}
        .page-cover{position:relative;min-height:100%;display:flex;align-items:center;justify-content:flex-start;overflow:hidden;color:#fff;padding:clamp(42px,4.6vw,72px);background:linear-gradient(145deg,#061326 0%,#092b49 100%)}
        .page-cover::before{content:"";position:absolute;inset:0;background:linear-gradient(115deg,rgba(255,255,255,.08) 0 1px,transparent 1px 42px);opacity:.22}
        .page-cover::after{content:"";position:absolute;right:42px;top:50%;width:150px;height:150px;border:1px solid rgba(255,255,255,.12);border-radius:50%;transform:translateY(-50%);box-shadow:-72px -62px 0 -30px rgba(255,138,28,.9),-130px 18px 0 -50px rgba(255,255,255,.14)}
        .page-cover.has-photo{padding:0;background:#071728}
        .page-cover-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
        .page-cover.has-photo::before{background:linear-gradient(180deg,rgba(7,23,40,.06),rgba(7,23,40,.7));opacity:1;z-index:1}
        .page-cover.has-photo::after{display:none}
        .page-cover-brand{position:relative;z-index:2;display:block;width:min(100%,360px);transform:translateY(18px)}
        .page-cover-brand strong{display:block;max-width:320px;font-size:27px;line-height:1.08;font-weight:950;letter-spacing:0}
        .page-cover-brand small{display:block;max-width:300px;margin-top:10px;color:#d8e4ef;font-size:13px;font-weight:850;line-height:1.55}
        @media(max-width:900px){.simple-page{padding:32px 0 48px}.page-box{grid-template-columns:1fr}.page-cover{min-height:220px;order:-1}.page-content{padding:28px}.page-content h1{font-size:34px}.body-text{font-size:16px}.about-heading-grid{grid-template-columns:1fr}}
    </style>
    @endpush
@endif

@section('content')
@include('public.partials.composition')
@endsection
