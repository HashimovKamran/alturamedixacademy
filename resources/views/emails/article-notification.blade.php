<!doctype html>
<html lang="az">
<head><meta charset="utf-8"></head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#071728">
<div style="max-width:620px;margin:0 auto;padding:24px">
    <div style="background:#fff;border:1px solid #e4ebf4;border-radius:18px;padding:24px">
        <h1 style="font-size:24px;margin:0 0 10px">{{ $article->title }}</h1>
        @if($article->excerpt)
            <p style="font-size:15px;line-height:1.7;color:#334155">{{ $article->excerpt }}</p>
        @endif
        <p style="font-size:14px;color:#64748b">Salam {{ $fullName ?: 'hörmətli istifadəçi' }}, yeni məqalə yayımlandı.</p>
        <p><a href="{{ $url }}" style="display:inline-block;background:#ff8a1c;color:#fff;text-decoration:none;border-radius:12px;padding:12px 18px;font-weight:bold">Məqaləni oxu</a></p>
    </div>
</div>
</body>
</html>
