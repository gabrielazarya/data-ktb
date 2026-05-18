<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jejak Murid</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
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
        <p>Persekutuan Kristen Antar Universitas &mdash; gerakan mahasiswa Kristen yang hadir untuk menjangkau, membangun, dan mengutus mahasiswa di kampus-kampus Indonesia.</p>
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
          <div class="value-icon">&#128330;&#65039;</div>
          <div><h4>Misi & Visi</h4><p>Menjangkau mahasiswa dengan Injil dan membangun pemimpin Kristen yang berkarakter Kristus.</p></div>
        </div>
        <div class="value-item">
          <div class="value-icon">&#128214;</div>
          <div><h4>Pendalaman Alkitab</h4><p>Belajar Firman Tuhan bersama dalam kelompok kecil yang saling menopang dan mendukung.</p></div>
        </div>
        <div class="value-item">
          <div class="value-icon">&#129309;</div>
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
      <p class="section-desc">Kelompok Tumbuh Bersama (KTB) adalah kelompok kecil beranggotakan 2&ndash;9 orang yang bertemu secara rutin untuk membahas bahan kekristenan, berdoa, dan bertumbuh bersama dalam iman.</p>
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
      <cite>&mdash; Amsal 27:17</cite>
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
        <div class="card-avatar">&#128081;</div>
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

<!-- VOICE OF STUDENT -->
<section id="vos">
  <div class="vos-header">
    <div class="section-tag">Dari Mereka</div>
    <h2 class="section-title">Voice of <span>Student</span></h2>
    <div class="divider" style="margin:1rem auto;"></div>
    <p class="section-desc">Kesaksian nyata dari mahasiswa yang mengalami pertumbuhan iman bersama Perkantas Surabaya. <a href="https://www.instagram.com/pmkkotasurabaya" target="_blank" style="color:#346739;font-weight:600;">@pmkkotasurabaya</a></p>
  </div>
  <div class="vos-track-wrap">
    <div class="vos-track">

      {{-- 1. Calvin (Terbaru) - DWlJNJgEzc0 --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_calvin.jpg') }}" alt="Voice of Student - Calvin" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Calvin &mdash; Telkom University Surabaya</div>
          <div class="vos-excerpt">Calvin mulai mengenal Kristus sejak kecil, namun sempat menyadari hanya mengenal Yesus secara pengetahuan. Melalui komunitas mahasiswa Kristen, ia belajar bahwa menjadi pengikut Kristus berarti hidup dalam ketaatan dan berani bersaksi.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DWlJNJgEzc0/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

      {{-- 2. Natanael - DVxTX2Ok50_ --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_natanael.jpg') }}" alt="Voice of Student - Natanael" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Natanael &mdash; Telkom University Surabaya</div>
          <div class="vos-excerpt">Dulu sering mempertanyakan arti hidup dan mencari kepuasan dari banyak hal yang terasa kosong. Melalui persekutuan dan pelayanan Misi, ia menemukan bahwa hanya di dalam Kristus hidupnya bermakna.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DVxTX2Ok50_/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

      {{-- 3. Gabriel - DVhdlnKE_P5 --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_gabriel.jpg') }}" alt="Voice of Student - Gabriel" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Gabriel &mdash; Telkom University Surabaya</div>
          <div class="vos-excerpt">Sempat mengalami pergumulan dan jatuh bangun dalam dosa. Melalui pelayanan Divisi Visitasi dan KTB, Tuhan memulihkan hidupnya. Kini dipercaya sebagai Koordinator Divisi Visitasi 2026.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DVhdlnKE_P5/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

      {{-- 4. Theo - DU-Bc3YErOK --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_theo.jpg') }}" alt="Voice of Student - Theo" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Theo &mdash; Universitas Negeri Surabaya</div>
          <div class="vos-excerpt">Merasa hidupnya sudah "aman" karena lahir di keluarga rohani. Namun lewat KTB, Tuhan membongkar "topeng rohani" yang ia pakai dan mengajarkan bahwa kepemimpinan sejati adalah kesetiaan, bukan ketenaran.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DU-Bc3YErOK/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

      {{-- 5. Marvin - DUNFlOyEnbt --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_marvin.jpg') }}" alt="Voice of Student - Marvin" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Marvin &mdash; Universitas Negeri Surabaya</div>
          <div class="vos-excerpt">Menemukan makna baru tentang tujuan hidup lewat pemuridan. Melalui KTB, ia belajar bahwa tujuan sejati bukan pencapaian pribadi, melainkan menjadi murid Kristus yang memuridkan orang lain.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DUNFlOyEnbt/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

      {{-- 6. Ina - DPJLIZ0Ehao --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_ina.jpg') }}" alt="Voice of Student - Ina" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Ina &mdash; Mahasiswi PMK Surabaya</div>
          <div class="vos-excerpt">Awalnya ragu ikut KTB karena takut mengganggu kuliah. Lewat perhatian para kakak, ia berani melangkah dan merasakan kasih yang nyata &mdash; belajar konsisten saat teduh dan bertumbuh sedikit demi sedikit.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DPJLIZ0Ehao/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

      {{-- 7. Naomi - DM-gjRnSh0J --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_naomi.jpg') }}" alt="Voice of Student - Naomi" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Naomi &mdash; Mahasiswi PMK Surabaya</div>
          <div class="vos-excerpt">Sejak kecil mengenal pemuridan dan mengalami kasih Tuhan secara pribadi. Kesaksiannya menegaskan: iman bukan sekadar pengetahuan, tapi perjumpaan pribadi yang nyata dengan Tuhan.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DM-gjRnSh0J/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

      {{-- 8. Voice of Student Perdana (Terlama) - DKXTLpnyB1N --}}
      <div class="vos-card">
        <img class="vos-img" src="{{ asset('images/vos/vos_perdana.jpg') }}" alt="Voice of Student - Perdana" loading="lazy">
        <div class="vos-body">
          <div class="vos-badge"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>Voice of Student</div>
          <div class="vos-name">Voice of Student &mdash; Perdana</div>
          <div class="vos-excerpt">Awal mula seri Voice of Student dari PMK Kota Surabaya &mdash; ajakan untuk berbagi cerita kasih dan bagaimana Tuhan bekerja dalam kehidupan setiap mahasiswa Kristen di Surabaya.</div>
          <a class="vos-link" href="https://www.instagram.com/p/DKXTLpnyB1N/" target="_blank">Baca ceritanya <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </div>
      </div>

    </div>
  </div>
  <div style="text-align:center;margin-top:2rem;">
    <a href="https://www.instagram.com/pmkkotasurabaya" target="_blank" style="display:inline-flex;align-items:center;gap:.6rem;background:#346739;color:#fff;padding:.75rem 1.8rem;border-radius:10px;font-weight:700;text-decoration:none;font-size:.9rem;transition:.2s;" onmouseover="this.style.background='#79AE6F'" onmouseout="this.style.background='#346739'">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
      Lihat Semua di Instagram
    </a>
  </div>
</section>

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
      <button class="btn-submit">Kirim Pesan ✝‰ï¸</button>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <p>&copy; 2025 <span>Jejak Murid</span> &mdash; Perkantas Surabaya. Dibuat dengan &#10084;&#65039; untuk pelayanan mahasiswa.</p>
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

