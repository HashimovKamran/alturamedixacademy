<!doctype html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin giriş</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <style>*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:"Noto Sans","Segoe UI",Arial,sans-serif;background:radial-gradient(circle at 9% 6%,rgba(184,229,231,.75),transparent 31%),radial-gradient(circle at 92% 2%,rgba(226,233,255,.82),transparent 29%),linear-gradient(135deg,#edf9f8 0%,#e7f5f7 48%,#e8f1fb 100%);display:grid;place-items:center;padding:24px;color:#151719}.login{width:min(430px,100%);background:#fff;border:1px solid #d8e9e7;border-radius:22px;padding:28px;box-shadow:0 26px 80px rgba(53,117,126,.13)}.logo{width:44px;height:44px;border-radius:13px;background:#111;color:#ffd166;display:grid;place-items:center;margin-bottom:18px;font-size:20px}h1{margin:0 0 8px;font-size:26px;font-weight:900;letter-spacing:-.025em}p{margin:0 0 22px;color:#63747a;font-weight:650;line-height:1.6}label{display:block;margin:13px 0 7px;font-size:13px;font-weight:900}input{width:100%;height:46px;border:1px solid #d8e9e7;border-radius:11px;padding:0 13px;font-size:14px;font-family:inherit;font-weight:700;outline:0}input:focus{border-color:#9fd7d0;box-shadow:0 0 0 4px rgba(117,214,200,.16)}button{width:100%;height:48px;border:0;border-radius:11px;background:#ffd166;color:#111;font-weight:900;font-size:14px;margin-top:18px;cursor:pointer;font-family:inherit;box-shadow:0 10px 22px rgba(255,209,102,.28)}.err{background:#fff1f1;color:#b42318;border:1px solid #fecaca;border-radius:13px;padding:12px;margin-bottom:16px;font-weight:850}.hint{margin-top:16px;background:#f4fbfa;border:1px solid #e8f1ef;border-radius:13px;padding:12px;color:#63747a;font-size:13px;font-weight:750}</style>
    <style>.logo{font-size:22px;line-height:1}.logo i{line-height:1}</style>
</head>
<body>
<div class="login">
    <div class="logo"><i class="ti ti-shield-heart"></i></div>
    <h1>Admin panel</h1>
    <p>Sayt kontentini, menyuları, sliderləri və ayarları idarə etmək üçün daxil olun.</p>
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('admin.login.store') }}">
        @csrf
        <label>İstifadəçi adı</label>
        <input type="text" name="username" value="{{ old('username', 'admin') }}" required>
        <label>Şifrə</label>
        <input type="password" name="password" required>
        <button type="submit">Daxil ol</button>
    </form>
    <div class="hint">İlk giriş: <b>admin</b> / <b>Admin123456</b></div>
</div>
</body>
</html>
