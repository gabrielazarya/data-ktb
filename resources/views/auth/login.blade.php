<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Sistem KTB Perkantas Surabaya</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--navy:#0f2544;--navy2:#1a3a6b;--gold:#f5a623;--gold2:#e8941a;--white:#ffffff;}
body{font-family:'Inter',sans-serif;min-height:100vh;background:var(--navy);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}

/* Background pattern */
body::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,#0a1a38 0%,var(--navy) 50%,#0d2250 100%);}
.bg-circle{position:absolute;border-radius:50%;opacity:.06;background:var(--gold);}
.bg-circle.c1{width:600px;height:600px;top:-200px;right:-100px;}
.bg-circle.c2{width:400px;height:400px;bottom:-150px;left:-100px;}
.bg-cross{position:absolute;font-size:20rem;color:rgba(255,255,255,.015);right:5%;top:50%;transform:translateY(-50%);user-select:none;}

/* Layout */
.login-wrapper{position:relative;z-index:1;width:100%;max-width:460px;padding:1.5rem;}

/* Logo top */
.login-logo{text-align:center;margin-bottom:2rem;}
.login-logo a{display:inline-flex;align-items:center;gap:.75rem;text-decoration:none;}
.login-logo img{height:44px;width:44px;object-fit:contain;}
.login-logo span{font-size:1.2rem;font-weight:700;color:var(--white);}
.login-logo span em{color:var(--gold);font-style:normal;}

/* Card */
.login-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:2.5rem;backdrop-filter:blur(20px);}
.card-title{font-size:1.6rem;font-weight:800;color:var(--white);margin-bottom:.35rem;}
.card-sub{font-size:.875rem;color:rgba(255,255,255,.5);margin-bottom:2rem;}

/* Alerts */
.alert-error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:.875rem 1rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.6rem;}
.alert-error span{font-size:.85rem;color:#fca5a5;}
.alert-icon{font-size:1rem;flex-shrink:0;margin-top:.05rem;}

/* Form */
.form-group{margin-bottom:1.25rem;}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:rgba(255,255,255,.65);margin-bottom:.5rem;letter-spacing:.02em;}
.input-wrap{position:relative;}
.input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:1rem;color:rgba(255,255,255,.3);pointer-events:none;}
.form-group input{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:.8rem 1rem .8rem 2.75rem;color:var(--white);font-family:inherit;font-size:.9rem;outline:none;transition:.2s;}
.form-group input::placeholder{color:rgba(255,255,255,.25);}
.form-group input:focus{border-color:var(--gold);background:rgba(255,255,255,.1);box-shadow:0 0 0 3px rgba(245,166,35,.15);}
.form-group input.is-invalid{border-color:rgba(239,68,68,.5);}
.invalid-msg{font-size:.75rem;color:#fca5a5;margin-top:.4rem;}

/* Password toggle */
.toggle-pass{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:rgba(255,255,255,.3);font-size:1rem;transition:.2s;padding:.2rem;}
.toggle-pass:hover{color:rgba(255,255,255,.7);}

/* Remember */
.form-footer{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;}
.remember{display:flex;align-items:center;gap:.5rem;cursor:pointer;}
.remember input{width:16px;height:16px;accent-color:var(--gold);cursor:pointer;}
.remember span{font-size:.825rem;color:rgba(255,255,255,.6);}
.forgot{font-size:.825rem;color:var(--gold);text-decoration:none;opacity:.8;transition:.2s;}
.forgot:hover{opacity:1;}

/* Submit */
.btn-login{width:100%;background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--navy);padding:.9rem;border:none;border-radius:12px;font-family:inherit;font-size:1rem;font-weight:800;cursor:pointer;transition:.2s;letter-spacing:.02em;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(245,166,35,.4);}
.btn-login:active{transform:translateY(0);}

/* Back link */
.back-link{text-align:center;margin-top:1.5rem;}
.back-link a{font-size:.8rem;color:rgba(255,255,255,.4);text-decoration:none;transition:.2s;}
.back-link a:hover{color:var(--gold);}
.back-link a span{font-size:.9rem;margin-right:.3rem;}

/* Loading state */
.btn-login.loading{opacity:.7;pointer-events:none;}
</style>
</head>
<body>
<div class="bg-circle c1"></div>
<div class="bg-circle c2"></div>
<div class="bg-cross">✝</div>

<div class="login-wrapper">
  <!-- Logo -->
  <div class="login-logo">
    <a href="{{ route('landing') }}">
      <img src="{{ asset('images/ktb_logo.png') }}" alt="Logo KTB">
      <span>Sistem <em>KTB</em></span>
    </a>
  </div>

  <!-- Card -->
  <div class="login-card">
    <h1 class="card-title">Selamat Datang 👋</h1>
    <p class="card-sub">Masuk ke akun Sistem KTB Perkantas Surabaya</p>

    {{-- Error Alert --}}
    @if ($errors->any())
    <div class="alert-error">
      <span class="alert-icon">⚠️</span>
      <span>{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Session Error --}}
    @if (session('error'))
    <div class="alert-error">
      <span class="alert-icon">⚠️</span>
      <span>{{ session('error') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
      @csrf

      <!-- Username -->
      <div class="form-group">
        <label for="username">Username</label>
        <div class="input-wrap">
          <span class="input-icon">👤</span>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Masukkan username"
            value="{{ old('username') }}"
            autocomplete="username"
            class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
            required
          >
        </div>
        @error('username')
        <p class="invalid-msg">{{ $message }}</p>
        @enderror
      </div>

      <!-- Password -->
      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrap">
          <span class="input-icon">🔒</span>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan password"
            autocomplete="current-password"
            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
            required
          >
          <button type="button" class="toggle-pass" onclick="togglePassword()" id="toggleBtn">👁️</button>
        </div>
        @error('password')
        <p class="invalid-msg">{{ $message }}</p>
        @enderror
      </div>

      <!-- Remember & Forgot -->
      <div class="form-footer">
        <label class="remember">
          <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
          <span>Ingat saya</span>
        </label>
        <a href="#" class="forgot">Lupa password?</a>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn-login" id="submitBtn">
        Masuk ke Sistem
      </button>
    </form>
  </div>

  <!-- Back -->
  <div class="back-link">
    <a href="{{ route('landing') }}"><span>←</span> Kembali ke halaman utama</a>
  </div>
</div>

<script>
function togglePassword() {
  const pwd = document.getElementById('password');
  const btn = document.getElementById('toggleBtn');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    btn.textContent = '🙈';
  } else {
    pwd.type = 'password';
    btn.textContent = '👁️';
  }
}

document.getElementById('loginForm').addEventListener('submit', function() {
  document.getElementById('submitBtn').textContent = 'Memproses...';
  document.getElementById('submitBtn').classList.add('loading');
});
</script>
</body>
</html>
