<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jejak Murid</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --navy:#346739;
  --navy2:#79AE6F;
  --gold:#9FCB98;
  --gold2:#79AE6F;
  --cream:#f4faf4;
  --gray:#6b7280;
  --white:#ffffff;
}
html{scroll-behavior:smooth;}
body{font-family:'Inter',sans-serif;background:var(--cream);color:#2d3a2e;}

/* NAV */
nav{position:fixed;top:0;left:0;right:0;z-index:999;padding:0 5%;display:flex;align-items:center;justify-content:space-between;height:70px;background:rgba(52,103,57,0.95);backdrop-filter:blur(12px);border-bottom:1px solid rgba(159,203,152,0.25);transition:.3s;}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
.nav-logo img{height:38px;width:38px;object-fit:contain;}
.nav-logo span{font-size:1.1rem;font-weight:700;color:var(--white);}
.nav-logo span em{color:var(--gold);font-style:normal;}
.nav-links{display:flex;align-items:center;gap:2rem;}
.nav-links a{color:rgba(255,255,255,0.85);text-decoration:none;font-size:.9rem;font-weight:500;transition:.2s;}
.nav-links a:hover{color:#ffffff;}
.btn-login{background:#ffffff;color:#346739!important;padding:.45rem 1.2rem;border-radius:8px;font-weight:700!important;transition:.2s!important;border:2px solid transparent;}
.btn-login:hover{background:#f0faf0;transform:translateY(-1px);}

/* HERO */
#home{min-height:100vh;background-size:cover;background-position:center;background-repeat:no-repeat;display:flex;align-items:center;justify-content:center;text-align:center;padding:120px 5% 80px;position:relative;overflow:hidden;}
#home::before{content:'';position:absolute;inset:0;background:rgba(0,0,0,0.45);}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.5);color:var(--white);padding:.4rem 1rem;border-radius:50px;font-size:.8rem;font-weight:600;margin-bottom:1.5rem;letter-spacing:.05em;}
.hero-badge::before{content:'✦';}
#home h1{font-size:clamp(2.2rem,5vw,4rem);font-weight:900;color:var(--white);line-height:1.1;margin-bottom:1.2rem;}
#home h1 span{color:#ffffff;text-shadow:0 2px 20px rgba(0,0,0,.25);}
#home p{font-size:1.1rem;color:rgba(255,255,255,0.7);max-width:600px;margin:0 auto 2.5rem;line-height:1.8;}
.hero-btns{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;}
.btn-primary{background:#ffffff;color:#346739;padding:.8rem 2rem;border-radius:10px;text-decoration:none;font-weight:700;font-size:.95rem;transition:.2s;box-shadow:0 4px 20px rgba(0,0,0,.2);}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.3);background:#f0faf0;}
.btn-outline{border:2px solid rgba(255,255,255,.7);color:var(--white);padding:.8rem 2rem;border-radius:10px;text-decoration:none;font-weight:600;font-size:.95rem;transition:.2s;}
.btn-outline:hover{border-color:#ffffff;background:rgba(255,255,255,.15);}
.hero-scroll{position:absolute;bottom:30px;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:.4rem;color:rgba(255,255,255,.6);font-size:.75rem;}
.scroll-dot{width:6px;height:6px;background:#ffffff;border-radius:50%;animation:scrollBounce 1.5s infinite;}
@keyframes scrollBounce{0%,100%{transform:translateY(0);}50%{transform:translateY(6px);}}

/* SECTION STYLES */
section{padding:90px 5%;}
.section-tag{display:inline-block;background:rgba(52,103,57,.1);color:#2e6b33;font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:.3rem .9rem;border-radius:50px;margin-bottom:1rem;border:1px solid rgba(52,103,57,.2);}
.section-title{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:800;color:#1e3d20;margin-bottom:1rem;line-height:1.2;}
.section-title span{color:#346739;}
.section-desc{font-size:1rem;color:#4b5e4d;line-height:1.8;max-width:600px;}
.divider{width:60px;height:4px;background:linear-gradient(90deg,#346739,#79AE6F);border-radius:2px;margin:1rem 0;}

/* PERKANTAS */
#perkantas{background:var(--white);}
.perkantas-grid{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;max-width:1100px;margin:0 auto;}
.perkantas-img-box{position:relative;}
.perkantas-card-big{background:linear-gradient(135deg,#1e4022,#346739);border-radius:20px;padding:3rem 2.5rem;color:white;position:relative;overflow:hidden;}
.perkantas-card-big::after{content:'✝';position:absolute;right:-20px;bottom:-30px;font-size:8rem;opacity:.08;color:white;}
.perkantas-card-big h3{font-size:1.6rem;font-weight:800;margin-bottom:.6rem;color:#ffffff;}
.perkantas-card-big p{font-size:.9rem;line-height:1.8;color:rgba(255,255,255,.85);}
.perkantas-stats{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1.5rem;}
.stat-item{background:rgba(255,255,255,.12);border-radius:12px;padding:1rem;text-align:center;border:1px solid rgba(255,255,255,.1);}
.stat-num{font-size:1.5rem;font-weight:800;color:#ffffff;}
.stat-label{font-size:.75rem;color:rgba(255,255,255,.75);}
.perkantas-text .section-desc{max-width:100%;}
.perkantas-values{display:flex;flex-direction:column;gap:1rem;margin-top:2rem;}
.value-item{display:flex;gap:1rem;align-items:flex-start;}
.value-icon{width:40px;height:40px;background:#346739;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;box-shadow:0 2px 8px rgba(52,103,57,.3);}
.value-item h4{font-weight:700;color:#1e3d20;margin-bottom:.2rem;}
.value-item p{font-size:.875rem;color:#4b5e4d;}

/* KTB */
#ktb{background:linear-gradient(180deg,var(--cream) 0%,#e8f5e8 100%);}
.ktb-inner{max-width:1100px;margin:0 auto;}
.ktb-header{text-align:center;margin-bottom:4rem;}
.ktb-header .section-desc{margin:0 auto;}
.ktb-flow{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;margin-bottom:3rem;}
.ktb-step{background:var(--white);border-radius:16px;padding:1.8rem;position:relative;box-shadow:0 2px 16px rgba(52,103,57,.08);border-top:3px solid #346739;transition:.3s;}
.ktb-step:hover{transform:translateY(-4px);box-shadow:0 8px 30px rgba(52,103,57,.15);}
.step-num{width:36px;height:36px;background:#346739;color:#ffffff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;margin-bottom:1rem;}
.ktb-step h4{font-weight:700;color:#1e3d20;margin-bottom:.5rem;}
.ktb-step p{font-size:.875rem;color:#4b5e4d;line-height:1.7;}
.ktb-quote{background:linear-gradient(135deg,#1e4022,#346739);border-radius:16px;padding:2.5rem;text-align:center;color:white;box-shadow:0 4px 24px rgba(52,103,57,.3);}
.ktb-quote blockquote{font-size:1.1rem;font-style:italic;line-height:1.8;color:rgba(255,255,255,.95);margin-bottom:1rem;}
.ktb-quote cite{color:#9FCB98;font-size:.85rem;font-weight:600;}

/* PROFILES */
#profil{background:var(--white);}
.profil-header{text-align:center;margin-bottom:4rem;}
.profil-header .section-desc{margin:0 auto;}
.cards-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem;max-width:1100px;margin:0 auto;}
.profile-card{border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);transition:.3s;position:relative;}
.profile-card:hover{transform:translateY(-6px);box-shadow:0 12px 40px rgba(0,0,0,.15);}
.card-header{padding:2rem 2rem 1.5rem;position:relative;}
.card-pkk .card-header{background:linear-gradient(135deg,#1e4022,#346739);}
.card-cpkk .card-header{background:linear-gradient(135deg,#346739,#79AE6F);}
.card-akk .card-header{background:linear-gradient(135deg,#79AE6F,#9FCB98);}
.card-role-badge{display:inline-block;background:rgba(255,255,255,.2);color:white;font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.25rem .8rem;border-radius:50px;margin-bottom:1rem;}
.card-avatar{width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:1rem;}
.card-header h3{font-size:1.25rem;font-weight:800;color:white;}
.card-header p{font-size:.85rem;color:rgba(255,255,255,.75);margin-top:.3rem;}
.card-body{padding:1.5rem 2rem 2rem;background:white;}
.card-body ul{list-style:none;display:flex;flex-direction:column;gap:.7rem;}
.card-body li{display:flex;align-items:flex-start;gap:.7rem;font-size:.875rem;color:#374151;}
.card-body li::before{content:'✓';color:#346739;font-weight:800;margin-top:.05rem;flex-shrink:0;}

/* KONTAK */
#kontak{background:linear-gradient(135deg,#1e4022,#346739);padding:90px 5%;}
.kontak-inner{max-width:700px;margin:0 auto;text-align:center;}
.kontak-inner .section-title{color:white;}
.kontak-inner .section-desc{color:rgba(255,255,255,.7);margin:0 auto 2.5rem;}
.kontak-form{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:2.5rem;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;}
.form-group{display:flex;flex-direction:column;gap:.4rem;}
.form-group label{font-size:.8rem;font-weight:600;color:rgba(255,255,255,.7);text-align:left;}
.form-group input,.form-group textarea{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:.75rem 1rem;color:white;font-family:inherit;font-size:.9rem;outline:none;transition:.2s;}
.form-group input::placeholder,.form-group textarea::placeholder{color:rgba(255,255,255,.35);}
.form-group input:focus,.form-group textarea:focus{border-color:#9FCB98;background:rgba(255,255,255,.15);}
.form-group.full{grid-column:1/-1;}
.form-group textarea{resize:vertical;min-height:120px;}
.btn-submit{width:100%;background:#ffffff;color:#346739;padding:.85rem;border:none;border-radius:10px;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;transition:.2s;margin-top:.5rem;box-shadow:0 2px 12px rgba(0,0,0,.15);}
.btn-submit:hover{background:#f0faf0;transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,0,0,.2);}

/* FOOTER */
footer{background:#0e1f10;padding:2rem 5%;text-align:center;color:rgba(255,255,255,.4);font-size:.8rem;}
footer span{color:#9FCB98;}

/* RESPONSIVE */
@media(max-width:768px){
  .perkantas-grid{grid-template-columns:1fr;}
  .form-row{grid-template-columns:1fr;}
  .nav-links a:not(.btn-login){display:none;}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="#home" class="nav-logo">
    <img src="{{ asset('images/ktb_logo.png') }}" alt="Logo KTB">
    <span>Jejak <em>Murid</em></span>
  </a>
  <div class="nav-links">
    <a href="#home">Home</a>
    <a href="#perkantas">Perkantas</a>
    <a href="#ktb">KTB</a>
    <a href="#kontak">Kontak Kami</a>
    <a href="{{ route('login') }}" class="btn-login">Log In</a>
  </div>
</nav>

<!-- HERO -->
<section id="home" style="background-image: url('{{ asset('images/hero_ktb.jpg') }}');">
  <div style="position:relative;z-index:1;">
    <h1>Bertumbuh Bersama<br>dalam <span>Iman & Kasih</span></h1>
    <p>Platform pelaporan dan manajemen Kelompok Tumbuh Bersama (KTB) untuk mendukung pertumbuhan rohani mahasiswa Kristen di Surabaya.</p>
    <div class="hero-btns">
      <a href="{{ route('login') }}" class="btn-primary">Masuk ke Sistem</a>
      <a href="#perkantas" class="btn-outline">Pelajari Lebih Lanjut</a>
    </div>
  </div>
  <div class="hero-scroll">
    <span>Scroll</span>
    <div class="scroll-dot"></div>
  </div>
</section>

<!-- PERKANTAS -->
<section id="perkantas">
  <div class="perkantas-grid">
    <div class="perkantas-img-box">
      <div class="perkantas-card-big">
        <h3>Perkantas</h3>
        <p>Persekutuan Kristen Antar Universitas — gerakan mahasiswa Kristen yang hadir untuk menjangkau, membangun, dan mengutus mahasiswa di kampus-kampus Indonesia.</p>
        <div class="perkantas-stats">
          <div class="stat-item"><div class="stat-num">14+</div><div class="stat-label">Kampus di Surabaya</div></div>
          <div class="stat-item"><div class="stat-num">KTB</div><div class="stat-label">Metode Utama</div></div>
          <div class="stat-item"><div class="stat-num">4</div><div class="stat-label">Role Pengguna</div></div>
          <div class="stat-item"><div class="stat-num">100%</div><div class="stat-label">Berbasis Relasi</div></div>
        </div>
      </div>
    </div>
    <div class="perkantas-text">
      <h2 class="section-title">Apa itu <span>Perkantas?</span></h2>
      <div class="divider"></div>
      <p class="section-desc">Perkantas (Persekutuan Kristen Antar Universitas) adalah organisasi pelayanan mahasiswa Kristen yang berfokus pada pertumbuhan iman melalui komunitas kecil yang intim dan penuh kasih.</p>
      <div class="perkantas-values">
        <div class="value-item">
          <div class="value-icon">🕊️</div>
          <div><h4>Misi & Visi</h4><p>Menjangkau mahasiswa dengan Injil dan membangun pemimpin Kristen yang berkarakter Kristus.</p></div>
        </div>
        <div class="value-item">
          <div class="value-icon">📖</div>
          <div><h4>Pendalaman Alkitab</h4><p>Belajar Firman Tuhan bersama dalam kelompok kecil yang saling menopang dan mendukung.</p></div>
        </div>
        <div class="value-item">
          <div class="value-icon">🤝</div>
          <div><h4>Komunitas Nyata</h4><p>Membangun relasi yang tulus antar mahasiswa Kristen lintas kampus di Surabaya.</p></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- KTB -->
<section id="ktb">
  <div class="ktb-inner">
    <div class="ktb-header">
      <h2 class="section-title">Apa itu <span>KTB?</span></h2>
      <div class="divider" style="margin:1rem auto;"></div>
      <p class="section-desc">Kelompok Tumbuh Bersama (KTB) adalah kelompok kecil beranggotakan 2–9 orang yang bertemu secara rutin untuk membahas bahan kekristenan, berdoa, dan bertumbuh bersama dalam iman.</p>
    </div>
    <div class="ktb-flow">
      <div class="ktb-step">
        <div class="step-num">01</div>
        <h4>Pertemuan Rutin</h4>
        <p>Kelompok bertemu secara konsisten setiap minggu atau dua minggu untuk belajar Alkitab bersama.</p>
      </div>
      <div class="ktb-step">
        <div class="step-num">02</div>
        <h4>Bahan Terstruktur</h4>
        <p>Membahas bahan kekristenan yang sudah disiapkan, memastikan pertumbuhan yang terarah dan mendalam.</p>
      </div>
      <div class="ktb-step">
        <div class="step-num">03</div>
        <h4>Relasi Mendalam</h4>
        <p>Membangun kepercayaan dan keterbukaan antar anggota melalui sharing kehidupan yang otentik.</p>
      </div>
      <div class="ktb-step">
        <div class="step-num">04</div>
        <h4>Proyek Ketaatan</h4>
        <p>Menerapkan iman dalam tindakan nyata melalui proyek-proyek ketaatan yang konkret dan terukur.</p>
      </div>
    </div>
    <div class="ktb-quote">
      <blockquote>"Besi menajamkan besi, orang menajamkan sesamanya."</blockquote>
      <cite>— Amsal 27:17</cite>
    </div>
  </div>
</section>

<!-- PROFIL -->
<section id="profil">
  <div class="profil-header">
    <h2 class="section-title">Mengenal <span>Peran</span> dalam KTB</h2>
    <div class="divider" style="margin:1rem auto;"></div>
    <p class="section-desc">Setiap orang memiliki peran yang berbeda dalam ekosistem KTB. Berikut adalah tiga peran utama yang ada dalam kelompok.</p>
  </div>
  <div class="cards-grid">
    <div class="profile-card card-pkk">
      <div class="card-header">
        <div class="card-role-badge">Pemimpin</div>
        <div class="card-avatar">👑</div>
        <h3>PKK</h3>
        <p>Pemimpin Kelompok Kecil</p>
      </div>
      <div class="card-body">
        <ul>
          <li>Memimpin dan memfasilitasi jalannya KTB</li>
          <li>Mencatat kehadiran setiap anggota KTB</li>
          <li>Menginput bahan dan progres KTB</li>
          <li>Memantau pertumbuhan setiap AKK</li>
          <li>Mengelola proyek ketaatan kelompok</li>
          <li>Melihat pohon KTB & grafik kehadiran</li>
        </ul>
      </div>
    </div>
    <div class="profile-card card-cpkk">
      <div class="card-header">
        <div class="card-role-badge">Calon Pemimpin</div>
        <div class="card-avatar">🌱</div>
        <h3>CPKK</h3>
        <p>Calon Pemimpin Kelompok Kecil</p>
      </div>
      <div class="card-body">
        <ul>
          <li>AKK yang sedang dipersiapkan menjadi PKK</li>
          <li>Dalam proses belajar kepemimpinan KTB</li>
          <li>Mengikuti bahan PRA bersama PKK pembimbing</li>
          <li>Melihat perkembangan diri sendiri di sistem</li>
          <li>Menerima mentoring dari PKK yang berpengalaman</li>
          <li>Tahap transisi menuju kemandirian pelayanan</li>
        </ul>
      </div>
    </div>
    <div class="profile-card card-akk">
      <div class="card-header">
        <div class="card-role-badge">Anggota</div>
        <div class="card-avatar">🙋</div>
        <h3>AKK</h3>
        <p>Anggota Kelompok Kecil</p>
      </div>
      <div class="card-body">
        <ul>
          <li>Mengikuti pertemuan KTB secara rutin</li>
          <li>Melihat riwayat kehadiran diri sendiri</li>
          <li>Memantau progres bahan yang sudah dipelajari</li>
          <li>Melihat proyek ketaatan yang berlangsung</li>
          <li>Berbagi melalui kolom curhatan pribadi</li>
          <li>Bertumbuh dalam iman dan karakter Kristus</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- KONTAK -->
<section id="kontak">
  <div class="kontak-inner">
    <h2 class="section-title">Kontak Kami</h2>
    <div class="divider" style="margin:1rem auto;"></div>
    <p class="section-desc">Ada pertanyaan tentang sistem KTB atau ingin bergabung dengan Perkantas Surabaya? Kirim pesan kepada kami.</p>
    <div class="kontak-form">
      <div class="form-row">
        <div class="form-group"><label>Nama Lengkap</label><input type="text" placeholder="Nama kamu"></div>
        <div class="form-group"><label>Kampus</label><input type="text" placeholder="Asal kampus"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Email</label><input type="email" placeholder="email@kampus.ac.id"></div>
        <div class="form-group"><label>No. WhatsApp</label><input type="tel" placeholder="08xx-xxxx-xxxx"></div>
      </div>
      <div class="form-row">
        <div class="form-group full"><label>Pesan</label><textarea placeholder="Tulis pesanmu di sini..."></textarea></div>
      </div>
      <button class="btn-submit">Kirim Pesan ✉️</button>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <p>© 2025 <span>Jejak Murid</span> — Perkantas Surabaya. Dibuat dengan ❤️ untuk pelayanan mahasiswa.</p>
</footer>

<script>
// Smooth navbar scroll effect
window.addEventListener('scroll',()=>{
  const nav=document.querySelector('nav');
  nav.style.background=window.scrollY>50?'rgba(52,103,57,0.98)':'rgba(52,103,57,0.95)';  
});

// Active nav link on scroll
const sections=document.querySelectorAll('section[id]');
const navLinks=document.querySelectorAll('.nav-links a[href^="#"]');
window.addEventListener('scroll',()=>{
  let cur='';
  sections.forEach(s=>{if(window.scrollY>=s.offsetTop-100)cur=s.id;});
  navLinks.forEach(a=>{
    a.style.color=a.getAttribute('href')==='#'+cur?'#9FCB98':'rgba(255,255,255,0.8)';
  });
});
</script>
</body>
</html>
