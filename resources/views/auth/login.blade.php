<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Jurnal Eklesia</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f6f0e6;color:#31251d;font-family:Arial,Helvetica,sans-serif;display:grid;grid-template-columns:1fr 1fr}.story{background:#762c12;color:#fff5e9;padding:8vw;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden}.story:after{content:"";position:absolute;width:480px;height:480px;border:1px solid #ffffff2b;border-radius:50%;bottom:-210px;right:-120px;box-shadow:0 0 0 45px #ffffff0b,0 0 0 100px #ffffff08}.brand{font-weight:900;font-size:19px;letter-spacing:-.4px}.mark{width:42px;height:42px;background:#f09a5c;color:#60240f;border-radius:13px;display:inline-grid;place-items:center;margin-right:10px}.story-copy{position:relative;z-index:1;max-width:530px}.eyebrow{font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#efb58e;font-weight:900}.story h1{font-size:clamp(40px,5vw,67px);line-height:1.04;letter-spacing:-2.8px;margin:18px 0}.story p{font-size:18px;line-height:1.7;color:#f3d6c2;max-width:470px}.story-foot{font-size:12px;color:#e6bca1;position:relative;z-index:1}.login-side{display:grid;place-items:center;padding:32px}.login-card{width:min(100%,430px)}.mobile-brand{display:none}.login-card h2{font-size:34px;letter-spacing:-1.1px;margin:0 0 8px;font-weight:900}.sub{color:#827468;margin:0 0 30px;line-height:1.6}.field{display:flex;flex-direction:column;gap:8px;margin-bottom:19px}.field label{font-size:14px;font-weight:800}.field input{padding:15px 14px;border:1px solid #ddcebd;border-radius:11px;background:#fffdf8;font-size:16px;outline:none}.field input:focus{border-color:#a9431d;box-shadow:0 0 0 3px #a9431d1d}.remember{display:flex;align-items:center;gap:9px;color:#6f6256;font-size:14px;margin:3px 0 22px}.remember input{accent-color:#a9431d;width:16px;height:16px}.btn{border:0;width:100%;padding:15px;background:#a9431d;border-radius:11px;color:#fff;font-weight:900;font-size:16px;cursor:pointer}.btn:hover{background:#762c12}.error{color:#a63e32;font-size:13px;margin-top:5px}.alert{padding:13px 14px;border-radius:10px;background:#f7e6e2;color:#a63e32;font-size:14px;font-weight:700;margin-bottom:18px}.hint{margin-top:24px;color:#9a8a7a;font-size:12px;line-height:1.6}@media(max-width:760px){body{display:block;background:#fffdf8}.story{display:none}.login-side{min-height:100vh;padding:25px}.mobile-brand{display:flex;align-items:center;font-weight:900;margin-bottom:55px}.login-card{max-width:440px}.login-card h2{font-size:31px}}
    </style>
</head>
<body>
    <section class="story">
        <div class="brand"><span class="mark">E</span> Jurnal Eklesia</div>
        <div class="story-copy">
            <div class="eyebrow">Catatan kerja, bertumbuh bersama</div>
            <h1>Setiap hari punya cerita.</h1>
            <p>Ruang sederhana untuk mencatat kontribusi, menjaga ritme kerja, dan melihat perjalanan tim Eklesia.</p>
        </div>
        <div class="story-foot">Jurnal internal · Eklesia</div>
    </section>
    <main class="login-side">
        <div class="login-card">
            <div class="mobile-brand"><span class="mark">E</span> Jurnal Eklesia</div>
            <h2>Selamat datang</h2>
            <p class="sub">Masuk menggunakan akun yang diberikan oleh admin.</p>
            @if($errors->any())
                <div class="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            @endif
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required autofocus>
                    @error('username')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')<div class="error">{{ $message }}</div>@enderror
                </div>
                <label class="remember"><input type="checkbox" name="remember"> Tetap masuk di perangkat ini</label>
                <button class="btn" type="submit">Masuk ke jurnal</button>
            </form>
            <p class="hint">Belum memiliki akun? Hubungi admin untuk mendapatkan akses.</p>
        </div>
    </main>
</body>
</html>
