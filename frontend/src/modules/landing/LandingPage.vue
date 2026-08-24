<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuth } from '@/core/auth/useAuth'
import logoImg from '@/assets/logo.png'

const router = useRouter()
const { isAuthenticated } = useAuth()

function goToApp() {
  if (isAuthenticated()) {
    router.push('/')
  } else {
    router.push('/login')
  }
}

const features = [
  {
    icon: 'tooth',
    title: 'Rekam Medis Digital',
    desc: 'Kelola rekam medis pasien secara digital dengan aman. Riwayat perawatan, odontogram, resep, dan foto X-ray tersimpan terpusat.',
  },
  {
    icon: 'calendar',
    title: 'Manajemen Jadwal',
    desc: 'Sistem penjadwalan appointment yang mudah digunakan. Kurangi no-show dengan notifikasi otomatis ke pasien.',
  },
  {
    icon: 'chart',
    title: 'Laporan & Analitik',
    desc: 'Pantau performa klinik Anda secara real-time. Laporan pendapatan, kunjungan pasien, dan produktivitas dokter.',
  },
  {
    icon: 'users',
    title: 'Multi-Cabang',
    desc: 'Kelola lebih dari satu klinik dalam satu platform. Data terpusat, manajemen tim lintas cabang lebih mudah.',
  },
  {
    icon: 'invoice',
    title: 'Billing & Invoice',
    desc: 'Buat invoice professional, kelola pembayaran, dan integrasikan dengan asuransi BPJS dan swasta dengan mudah.',
  },
  {
    icon: 'shield',
    title: 'Keamanan Data',
    desc: 'Data pasien terenkripsi dan aman. Sistem backup otomatis dengan standar keamanan medis internasional.',
  },
]

const plan = {
  code: 'professional',
  name: 'My Dent Care',
  price: 299000,
  branches: 1,
  storage: 10,
  trialDays: 30,
}

const testimonials = [
  {
    name: 'drg. Sarah Amelia',
    clinic: 'Klinik Gigi Sehat, Jakarta',
    text: 'My Dent Care mengubah cara kami mengelola klinik. Proses yang dulu memakan 2 jam kini selesai dalam 15 menit.',
    avatar: 'SA',
  },
  {
    name: 'drg. Budi Santoso',
    clinic: 'Dental Care Plus, Surabaya',
    text: 'Rekam medis digital dan jadwal otomatis sangat membantu. Pasien pun lebih puas dengan pelayanan kami.',
    avatar: 'BS',
  },
  {
    name: 'drg. Rina Kusuma',
    clinic: 'Smile Dental Clinic, Bandung',
    text: 'Laporan keuangan yang detail membantu saya memantau perkembangan klinik. Highly recommended!',
    avatar: 'RK',
  },
]

const stats = [
  { value: '500+', label: 'Klinik Aktif' },
  { value: '150K+', label: 'Pasien Terdaftar' },
  { value: '99.9%', label: 'Uptime' },
  { value: '24/7', label: 'Support' },
]
</script>

<template>
  <div class="landing-page">

    <!-- ===== NAVBAR ===== -->
    <nav class="landing-nav">
      <div class="nav-inner">
        <div class="nav-logo">
          <img :src="logoImg" alt="My Dent Care" class="logo-img" />
          <div class="logo-text-wrap">
            <span class="logo-name">My Dent Care</span>
            <span class="logo-tagline">Aplikasi Management Klinik Gigi</span>
          </div>
        </div>
        <div class="nav-links">
          <a href="#features" class="nav-link">Fitur</a>
          <a href="#pricing" class="nav-link">Harga</a>
          <a href="#testimonials" class="nav-link">Testimoni</a>
        </div>
        <div class="nav-actions">
          <button @click="router.push('/login')" class="btn-nav-outline">Masuk</button>
          <button @click="goToApp" class="btn-nav-solid">Coba Gratis</button>
        </div>
      </div>
    </nav>

    <!-- ===== HERO ===== -->
    <section class="hero-section">
      <div class="hero-bg-blur hero-bg-blur-1"></div>
      <div class="hero-bg-blur hero-bg-blur-2"></div>
      <div class="hero-content">
        <div class="hero-badge">
          <span class="badge-dot"></span>
          Dipercaya 500+ Klinik Gigi di Indonesia
        </div>
        <h1 class="hero-title">
          Sistem Manajemen<br />
          <span class="hero-title-highlight">Klinik Gigi</span><br />
          Terlengkap
        </h1>
        <p class="hero-desc">
          My Dent Care mengintegrasikan rekam medis, penjadwalan, billing, dan analitik
          dalam satu platform yang mudah digunakan. Fokus pada pasien, biarkan kami
          urus operasional klinik Anda.
        </p>
        <div class="hero-cta">
          <button @click="goToApp" class="btn-hero-primary">
            Mulai Gratis 14 Hari
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </button>
          <button class="btn-hero-outline">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Lihat Demo
          </button>
        </div>
        <p class="hero-note">Tidak perlu kartu kredit &middot; Setup 5 menit &middot; Batalkan kapan saja</p>
      </div>

      <!-- Hero Visual / Mock UI -->
      <div class="hero-visual">
        <div class="mock-card mock-card-main">
          <div class="mock-card-header">
            <div class="mock-dot mock-dot-red"></div>
            <div class="mock-dot mock-dot-yellow"></div>
            <div class="mock-dot mock-dot-green"></div>
            <span class="mock-title">Dashboard — My Dent Care</span>
          </div>
          <div class="mock-body">
            <div class="mock-stats">
              <div class="mock-stat">
                <div class="mock-stat-val mock-stat-blue">48</div>
                <div class="mock-stat-label">Pasien Hari Ini</div>
              </div>
              <div class="mock-stat">
                <div class="mock-stat-val mock-stat-teal">12</div>
                <div class="mock-stat-label">Appointment</div>
              </div>
              <div class="mock-stat">
                <div class="mock-stat-val mock-stat-green">Rp 4.2jt</div>
                <div class="mock-stat-label">Pendapatan</div>
              </div>
            </div>
            <div class="mock-bar-group">
              <div class="mock-bar-label">Kunjungan Minggu Ini</div>
              <div class="mock-bars">
                <div class="mock-bar" style="height:40%"></div>
                <div class="mock-bar" style="height:65%"></div>
                <div class="mock-bar" style="height:55%"></div>
                <div class="mock-bar" style="height:80%"></div>
                <div class="mock-bar" style="height:70%"></div>
                <div class="mock-bar mock-bar-active" style="height:90%"></div>
                <div class="mock-bar" style="height:60%"></div>
              </div>
            </div>
            <div class="mock-appt-list">
              <div class="mock-appt-title">Appointment Hari Ini</div>
              <div v-for="i in 3" :key="i" class="mock-appt-item">
                <div class="mock-appt-avatar">P{{ i }}</div>
                <div class="mock-appt-info">
                  <div class="mock-appt-name">Pasien {{ ['Andi', 'Budi', 'Citra'][i - 1] }}</div>
                  <div class="mock-appt-time">{{ ['09:00', '10:30', '13:00'][i - 1] }} — drg. Sarah</div>
                </div>
                <span class="mock-appt-badge" :class="['mock-badge-green','mock-badge-blue','mock-badge-yellow'][i-1]">
                  {{ ['Selesai', 'Berlangsung', 'Menunggu'][i - 1] }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <!-- Floating cards -->
        <div class="mock-float mock-float-1">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20" class="mock-float-icon">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <div class="mock-float-title">Rekam Medis Tersimpan</div>
            <div class="mock-float-sub">Pasien: Andi W. — Tadi saja</div>
          </div>
        </div>
        <div class="mock-float mock-float-2">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20" class="mock-float-icon-teal">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          <div>
            <div class="mock-float-title">+12% Pendapatan</div>
            <div class="mock-float-sub">Dibanding bulan lalu</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== STATS ===== -->
    <section class="stats-section">
      <div class="stats-inner">
        <div v-for="s in stats" :key="s.label" class="stat-item">
          <div class="stat-value">{{ s.value }}</div>
          <div class="stat-label">{{ s.label }}</div>
        </div>
      </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section id="features" class="features-section">
      <div class="section-inner">
        <div class="section-header">
          <span class="section-badge">Fitur Unggulan</span>
          <h2 class="section-title">Semua yang Anda Butuhkan<br /><span class="text-gradient-medical">dalam Satu Platform</span></h2>
          <p class="section-desc">Dirancang khusus untuk kebutuhan klinik gigi modern di Indonesia</p>
        </div>
        <div class="features-grid">
          <div v-for="f in features" :key="f.title" class="feature-card hover-lift">
            <div class="feature-icon-wrap">
              <!-- tooth -->
              <svg v-if="f.icon === 'tooth'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              <!-- calendar -->
              <svg v-else-if="f.icon === 'calendar'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <!-- chart -->
              <svg v-else-if="f.icon === 'chart'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <!-- users -->
              <svg v-else-if="f.icon === 'users'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <!-- invoice -->
              <svg v-else-if="f.icon === 'invoice'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
              </svg>
              <!-- shield -->
              <svg v-else-if="f.icon === 'shield'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="feature-title">{{ f.title }}</h3>
            <p class="feature-desc">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== HOW IT WORKS ===== -->
    <section class="how-section">
      <div class="section-inner">
        <div class="section-header">
          <span class="section-badge">Cara Kerja</span>
          <h2 class="section-title">Mulai dalam <span class="text-gradient-medical">3 Langkah Mudah</span></h2>
        </div>
        <div class="steps-grid">
          <div class="step-item">
            <div class="step-number">1</div>
            <h3 class="step-title">Daftar & Setup</h3>
            <p class="step-desc">Buat akun dalam 2 menit. Isi profil klinik dan tambahkan tim dokter Anda.</p>
          </div>
          <div class="step-arrow">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </div>
          <div class="step-item">
            <div class="step-number">2</div>
            <h3 class="step-title">Import Data</h3>
            <p class="step-desc">Impor data pasien dari sistem lama Anda atau mulai dari awal dengan mudah.</p>
          </div>
          <div class="step-arrow">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </div>
          <div class="step-item">
            <div class="step-number">3</div>
            <h3 class="step-title">Mulai Beroperasi</h3>
            <p class="step-desc">Klinik Anda siap beroperasi. Nikmati efisiensi pengelolaan yang jauh lebih baik.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== PRICING ===== -->
    <section id="pricing" class="pricing-section">
      <div class="section-inner">
        <div class="section-header">
          <span class="section-badge">Harga</span>
          <h2 class="section-title">Satu Harga, <span class="text-gradient-medical">Semua Fitur</span></h2>
          <p class="section-desc">Tidak ada biaya tersembunyi. Nikmati free trial 1 bulan penuh tanpa perlu kartu kredit.</p>
        </div>

        <!-- Single Plan Card -->
        <div class="pricing-single-wrap">
          <div class="pricing-single-card">
            <!-- Left: Price -->
            <div class="pricing-single-left">
              <div class="pricing-single-trial-badge">FREE TRIAL 1 BULAN</div>
              <h3 class="pricing-single-name">My Dent Care</h3>
              <div class="pricing-single-price">
                <span class="pricing-single-currency">Rp</span>
                <span class="pricing-single-amount">299k</span>
                <div class="pricing-single-period">
                  <span>/bulan</span>
                  <span class="pricing-single-per">per cabang</span>
                </div>
              </div>
              <p class="pricing-single-note">Setelah trial berakhir. Batalkan kapan saja.</p>
              <button @click="goToApp" class="pricing-single-btn">
                Mulai Trial Gratis 1 Bulan
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </button>
            </div>

            <!-- Divider -->
            <div class="pricing-single-divider"></div>

            <!-- Right: Features -->
            <div class="pricing-single-right">
              <p class="pricing-single-features-title">Semua yang Anda dapatkan:</p>
              <ul class="pricing-single-features">
                <li>
                  <svg class="check-icon" fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span><strong>1 Cabang</strong> klinik</span>
                </li>
                <li>
                  <svg class="check-icon" fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span><strong>Unlimited</strong> pengguna & dokter</span>
                </li>
                <li>
                  <svg class="check-icon" fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span><strong>Unlimited</strong> rekam medis & odontogram</span>
                </li>
                <li>
                  <svg class="check-icon" fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span>Penjadwalan & appointment otomatis</span>
                </li>
                <li>
                  <svg class="check-icon" fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span>Billing, invoice & laporan keuangan</span>
                </li>
                <li>
                  <svg class="check-icon" fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span>{{ plan.storage }} GB penyimpanan data & foto</span>
                </li>
                <li>
                  <svg class="check-icon" fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span>Support 24/7 via WhatsApp & email</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Trust note -->
          <p class="pricing-trust-note">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" style="display:inline;vertical-align:middle;margin-right:4px;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Tidak perlu kartu kredit &middot; Setup 5 menit &middot; Data 100% aman &middot; Batalkan kapan saja
          </p>
        </div>
      </div>
    </section>

    <!-- ===== TESTIMONIALS ===== -->
    <section id="testimonials" class="testimonials-section">
      <div class="section-inner">
        <div class="section-header">
          <span class="section-badge">Testimoni</span>
          <h2 class="section-title">Dipercaya Dokter Gigi <span class="text-gradient-medical">di Seluruh Indonesia</span></h2>
        </div>
        <div class="testimonials-grid">
          <div v-for="t in testimonials" :key="t.name" class="testimonial-card hover-lift">
            <div class="testimonial-quote">"</div>
            <p class="testimonial-text">{{ t.text }}</p>
            <div class="testimonial-author">
              <div class="testimonial-avatar">{{ t.avatar }}</div>
              <div>
                <div class="testimonial-name">{{ t.name }}</div>
                <div class="testimonial-clinic">{{ t.clinic }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CTA BANNER ===== -->
    <section class="cta-section">
      <div class="cta-inner">
        <h2 class="cta-title">Siap Transformasi Klinik Anda?</h2>
        <p class="cta-desc">Bergabung dengan 500+ klinik gigi yang sudah merasakan manfaat My Dent Care. Mulai trial gratis 14 hari sekarang.</p>
        <div class="cta-btns">
          <button @click="goToApp" class="btn-cta-white">
            Mulai Gratis Sekarang
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </button>
          <button class="btn-cta-outline">Hubungi Tim Kami</button>
        </div>
      </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="landing-footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <div class="nav-logo" style="margin-bottom:0.75rem">
            <img :src="logoImg" alt="My Dent Care" class="logo-img" />
            <div class="logo-text-wrap">
              <span class="logo-name">My Dent Care</span>
              <span class="logo-tagline">Aplikasi Management Klinik Gigi</span>
            </div>
          </div>
          <p class="footer-tagline">Solusi manajemen klinik gigi profesional untuk Indonesia.</p>
        </div>
        <div class="footer-links-group">
          <h4 class="footer-group-title">Produk</h4>
          <a href="#features" class="footer-link">Fitur</a>
          <a href="#pricing" class="footer-link">Harga</a>
          <a href="#" class="footer-link">Changelog</a>
        </div>
        <div class="footer-links-group">
          <h4 class="footer-group-title">Perusahaan</h4>
          <a href="#" class="footer-link">Tentang Kami</a>
          <a href="#" class="footer-link">Blog</a>
          <a href="#" class="footer-link">Karir</a>
        </div>
        <div class="footer-links-group">
          <h4 class="footer-group-title">Support</h4>
          <a href="#" class="footer-link">Dokumentasi</a>
          <a href="#" class="footer-link">Kontak</a>
          <a href="#" class="footer-link">Status</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span>&copy; 2026 My Dent Care. All rights reserved.</span>
        <span>Made with care for Indonesian dental clinics</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ===== BASE ===== */
.landing-page {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
  color: #1f2937;
  background: #fff;
  overflow-x: hidden;
}

/* ===== NAVBAR ===== */
.landing-nav {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid #e5e7eb;
}
.nav-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
}
.nav-logo {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  text-decoration: none;
}
.logo-img {
  height: 48px;
  width: auto;
  object-fit: contain;
  display: block;
  flex-shrink: 0;
}
.logo-text-wrap {
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.logo-name {
  font-size: 1.1rem;
  font-weight: 800;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.2;
}
.logo-tagline {
  font-size: 0.6875rem;
  color: #9ca3af;
  font-weight: 500;
  line-height: 1;
  white-space: nowrap;
}
.nav-links {
  display: flex;
  gap: 2rem;
}
.nav-link {
  color: #6b7280;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9375rem;
  transition: color 0.2s;
}
.nav-link:hover { color: #0ea5e9; }
.nav-actions {
  display: flex;
  gap: 0.75rem;
}
.btn-nav-outline {
  padding: 0.5rem 1.125rem;
  border: 2px solid #0ea5e9;
  color: #0ea5e9;
  background: transparent;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
}
.btn-nav-outline:hover { background: #0ea5e9; color: white; }
.btn-nav-solid {
  padding: 0.5rem 1.125rem;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  box-shadow: 0 4px 12px rgba(14,165,233,0.3);
}
.btn-nav-solid:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(14,165,233,0.4); }

/* ===== HERO ===== */
.hero-section {
  min-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 3rem;
  padding: 5rem 1.5rem 4rem;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
}
.hero-bg-blur {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
  z-index: -1;
}
.hero-bg-blur-1 {
  width: 500px;
  height: 500px;
  background: rgba(14, 165, 233, 0.1);
  top: -100px;
  right: -100px;
}
.hero-bg-blur-2 {
  width: 400px;
  height: 400px;
  background: rgba(20, 184, 166, 0.08);
  bottom: 0;
  left: -100px;
}
.hero-content {
  flex: 1;
  max-width: 560px;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #bae6fd, #99f6e4);
  color: #0369a1;
  padding: 0.375rem 1rem;
  border-radius: 9999px;
  font-size: 0.8125rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}
.badge-dot {
  width: 8px;
  height: 8px;
  background: #0ea5e9;
  border-radius: 50%;
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.1;
  color: #111827;
  margin-bottom: 1.25rem;
  letter-spacing: -0.02em;
}
.hero-title-highlight {
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-desc {
  font-size: 1.125rem;
  color: #6b7280;
  line-height: 1.7;
  margin-bottom: 2rem;
}
.hero-cta {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}
.btn-hero-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 8px 24px rgba(14,165,233,0.35);
}
.btn-hero-primary:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(14,165,233,0.45); }
.btn-hero-outline {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: white;
  color: #374151;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-hero-outline:hover { border-color: #0ea5e9; color: #0ea5e9; background: #f0f9ff; }
.hero-note {
  font-size: 0.8125rem;
  color: #9ca3af;
}

/* ===== HERO VISUAL ===== */
.hero-visual {
  flex: 1;
  max-width: 520px;
  position: relative;
}
.mock-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.12);
  overflow: hidden;
  border: 1px solid #e5e7eb;
}
.mock-card-header {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.mock-dot {
  width: 12px; height: 12px;
  border-radius: 50%;
}
.mock-dot-red { background: #ef4444; }
.mock-dot-yellow { background: #f59e0b; }
.mock-dot-green { background: #10b981; }
.mock-title {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-left: 0.5rem;
}
.mock-body { padding: 1.25rem; }
.mock-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}
.mock-stat {
  background: #f9fafb;
  border-radius: 10px;
  padding: 0.75rem;
  text-align: center;
}
.mock-stat-val {
  font-size: 1.25rem;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 0.25rem;
}
.mock-stat-blue { color: #0ea5e9; }
.mock-stat-teal { color: #14b8a6; }
.mock-stat-green { color: #10b981; }
.mock-stat-label { font-size: 0.6875rem; color: #9ca3af; }
.mock-bar-group { margin-bottom: 1.25rem; }
.mock-bar-label { font-size: 0.75rem; color: #6b7280; margin-bottom: 0.5rem; font-weight: 500; }
.mock-bars {
  display: flex;
  align-items: flex-end;
  gap: 6px;
  height: 60px;
}
.mock-bar {
  flex: 1;
  background: #e5e7eb;
  border-radius: 4px 4px 0 0;
  transition: height 0.3s;
}
.mock-bar-active { background: linear-gradient(to top, #0ea5e9, #14b8a6); }
.mock-appt-list {}
.mock-appt-title { font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.625rem; }
.mock-appt-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
}
.mock-appt-item:last-child { border-bottom: none; }
.mock-appt-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  border-radius: 8px;
  color: white;
  font-size: 0.6875rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.mock-appt-info { flex: 1; }
.mock-appt-name { font-size: 0.8125rem; font-weight: 600; color: #1f2937; }
.mock-appt-time { font-size: 0.6875rem; color: #9ca3af; }
.mock-appt-badge {
  font-size: 0.625rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 9999px;
  text-transform: uppercase;
}
.mock-badge-green { background: #d1fae5; color: #065f46; }
.mock-badge-blue { background: #dbeafe; color: #1e40af; }
.mock-badge-yellow { background: #fef3c7; color: #92400e; }

/* Floating cards */
.mock-float {
  position: absolute;
  background: white;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 0.8125rem;
  border: 1px solid #e5e7eb;
  min-width: 200px;
}
.mock-float-1 { bottom: -20px; left: -40px; }
.mock-float-2 { top: 40px; right: -30px; }
.mock-float-icon { color: #10b981; flex-shrink: 0; }
.mock-float-icon-teal { color: #14b8a6; flex-shrink: 0; }
.mock-float-title { font-weight: 600; color: #1f2937; }
.mock-float-sub { font-size: 0.6875rem; color: #9ca3af; }

/* ===== STATS ===== */
.stats-section {
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  padding: 3rem 1.5rem;
}
.stats-inner {
  max-width: 900px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  text-align: center;
}
.stat-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: white;
  line-height: 1;
  margin-bottom: 0.375rem;
}
.stat-label {
  font-size: 0.9375rem;
  color: rgba(255,255,255,0.8);
  font-weight: 500;
}

/* ===== SHARED SECTION STYLES ===== */
.section-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}
.section-header {
  text-align: center;
  margin-bottom: 3.5rem;
}
.section-badge {
  display: inline-block;
  background: linear-gradient(135deg, #bae6fd, #99f6e4);
  color: #0369a1;
  padding: 0.3rem 0.875rem;
  border-radius: 9999px;
  font-size: 0.8125rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
}
.section-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: #111827;
  line-height: 1.2;
  margin-bottom: 1rem;
  letter-spacing: -0.02em;
}
.section-desc {
  font-size: 1.0625rem;
  color: #6b7280;
  max-width: 560px;
  margin: 0 auto;
  line-height: 1.6;
}

/* ===== FEATURES ===== */
.features-section {
  padding: 6rem 1.5rem;
  background: #f9fafb;
}
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
}
.feature-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  border: 1px solid #f3f4f6;
  transition: all 0.3s ease;
}
.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(14,165,233,0.15);
  border-color: #bae6fd;
}
.feature-icon-wrap {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #bae6fd, #99f6e4);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0284c7;
  margin-bottom: 1.25rem;
}
.feature-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.625rem;
}
.feature-desc {
  font-size: 0.9375rem;
  color: #6b7280;
  line-height: 1.65;
}

/* ===== HOW IT WORKS ===== */
.how-section {
  padding: 6rem 1.5rem;
  background: white;
}
.steps-grid {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}
.step-item {
  flex: 1;
  min-width: 220px;
  max-width: 280px;
  text-align: center;
}
.step-number {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: white;
  font-size: 1.5rem;
  font-weight: 800;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  box-shadow: 0 8px 20px rgba(14,165,233,0.35);
}
.step-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.625rem;
}
.step-desc {
  font-size: 0.9375rem;
  color: #6b7280;
  line-height: 1.6;
}
.step-arrow {
  color: #d1d5db;
  flex-shrink: 0;
}

/* ===== PRICING ===== */
.pricing-section {
  padding: 6rem 1.5rem;
  background: #f9fafb;
}
.check-icon { color: #10b981; flex-shrink: 0; }

/* Single plan layout */
.pricing-single-wrap {
  max-width: 860px;
  margin: 0 auto;
  text-align: center;
}
.pricing-single-card {
  display: flex;
  background: white;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(14, 165, 233, 0.15);
  border: 2px solid #bae6fd;
  overflow: hidden;
  text-align: left;
}
/* Left side */
.pricing-single-left {
  flex: 0 0 300px;
  background: linear-gradient(160deg, #0ea5e9 0%, #14b8a6 100%);
  padding: 2.5rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.pricing-single-trial-badge {
  display: inline-block;
  background: rgba(255,255,255,0.25);
  color: white;
  font-size: 0.6875rem;
  font-weight: 800;
  padding: 4px 14px;
  border-radius: 9999px;
  letter-spacing: 0.07em;
  width: fit-content;
  border: 1px solid rgba(255,255,255,0.4);
}
.pricing-single-name {
  font-size: 1.75rem;
  font-weight: 800;
  color: white;
  line-height: 1.1;
}
.pricing-single-price {
  display: flex;
  align-items: flex-end;
  gap: 4px;
  margin: 0.5rem 0;
}
.pricing-single-currency {
  font-size: 1.125rem;
  font-weight: 700;
  color: rgba(255,255,255,0.85);
  padding-bottom: 8px;
}
.pricing-single-amount {
  font-size: 4rem;
  font-weight: 900;
  color: white;
  line-height: 1;
}
.pricing-single-period {
  display: flex;
  flex-direction: column;
  padding-bottom: 6px;
  gap: 2px;
}
.pricing-single-period span:first-child {
  font-size: 1rem;
  color: rgba(255,255,255,0.85);
  font-weight: 600;
}
.pricing-single-per {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.65);
}
.pricing-single-note {
  font-size: 0.8125rem;
  color: rgba(255,255,255,0.7);
  line-height: 1.4;
}
.pricing-single-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: auto;
  padding: 0.875rem 1.5rem;
  background: white;
  color: #0ea5e9;
  border: none;
  border-radius: 12px;
  font-size: 0.9375rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}
.pricing-single-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(0,0,0,0.2);
}
/* Divider */
.pricing-single-divider {
  width: 1px;
  background: #e5e7eb;
  flex-shrink: 0;
}
/* Right side */
.pricing-single-right {
  flex: 1;
  padding: 2.5rem 2rem;
}
.pricing-single-features-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1.5rem;
}
.pricing-single-features {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem 1.5rem;
}
.pricing-single-features li {
  display: flex;
  align-items: flex-start;
  gap: 0.625rem;
  font-size: 0.9375rem;
  color: #374151;
  line-height: 1.4;
}
/* Trust note */
.pricing-trust-note {
  margin-top: 1.5rem;
  font-size: 0.8125rem;
  color: #9ca3af;
  text-align: center;
}

/* ===== TESTIMONIALS ===== */
.testimonials-section {
  padding: 6rem 1.5rem;
  background: white;
}
.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
}
.testimonial-card {
  background: #f9fafb;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #f3f4f6;
  transition: all 0.3s;
}
.testimonial-card:hover {
  background: white;
  box-shadow: 0 12px 36px rgba(14,165,233,0.12);
  border-color: #bae6fd;
}
.testimonial-quote {
  font-size: 4rem;
  line-height: 1;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-family: Georgia, serif;
  margin-bottom: -0.5rem;
}
.testimonial-text {
  font-size: 0.9375rem;
  color: #374151;
  line-height: 1.7;
  margin-bottom: 1.5rem;
  font-style: italic;
}
.testimonial-author {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}
.testimonial-avatar {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  border-radius: 12px;
  color: white;
  font-weight: 700;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.testimonial-name { font-weight: 700; color: #111827; font-size: 0.9375rem; }
.testimonial-clinic { font-size: 0.8125rem; color: #9ca3af; }

/* ===== CTA SECTION ===== */
.cta-section {
  padding: 6rem 1.5rem;
  background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #14b8a6 100%);
  text-align: center;
}
.cta-inner { max-width: 700px; margin: 0 auto; }
.cta-title {
  font-size: 2.75rem;
  font-weight: 800;
  color: white;
  margin-bottom: 1rem;
  letter-spacing: -0.02em;
}
.cta-desc {
  font-size: 1.0625rem;
  color: rgba(255,255,255,0.85);
  line-height: 1.65;
  margin-bottom: 2.5rem;
}
.cta-btns {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}
.btn-cta-white {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.9375rem 2.25rem;
  background: white;
  color: #0ea5e9;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}
.btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,0,0,0.2); }
.btn-cta-outline {
  padding: 0.9375rem 2.25rem;
  background: transparent;
  color: white;
  border: 2px solid rgba(255,255,255,0.6);
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cta-outline:hover { background: rgba(255,255,255,0.15); border-color: white; }

/* ===== FOOTER ===== */
.landing-footer {
  background: #111827;
  padding: 4rem 1.5rem 0;
}
.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 3rem;
  padding-bottom: 3rem;
  border-bottom: 1px solid #374151;
}
.footer-tagline {
  font-size: 0.9375rem;
  color: #9ca3af;
  line-height: 1.6;
  max-width: 260px;
}
.footer-links-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.footer-group-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: white;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.25rem;
}
.footer-link {
  font-size: 0.9375rem;
  color: #9ca3af;
  text-decoration: none;
  transition: color 0.2s;
}
.footer-link:hover { color: #0ea5e9; }
.footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem 0;
  display: flex;
  justify-content: space-between;
  font-size: 0.8125rem;
  color: #6b7280;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .hero-section { flex-direction: column; padding-top: 3rem; min-height: auto; }
  .hero-content { max-width: 100%; }
  .hero-visual { max-width: 480px; margin: 0 auto; }
  .hero-title { font-size: 2.75rem; }
  .features-grid, .testimonials-grid { grid-template-columns: repeat(2, 1fr); }
  .stats-inner { grid-template-columns: repeat(2, 1fr); }
  .footer-inner { grid-template-columns: 1fr 1fr; }
  .pricing-single-features { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .pricing-single-card { flex-direction: column; }
  .pricing-single-left { flex: none; }
  .pricing-single-divider { width: 100%; height: 1px; }
}
@media (max-width: 640px) {
  .nav-links { display: none; }
  .hero-title { font-size: 2.25rem; }
  .section-title { font-size: 1.875rem; }
  .features-grid, .testimonials-grid { grid-template-columns: 1fr; }
  .stats-inner { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
  .steps-grid { flex-direction: column; }
  .step-arrow { display: none; }
  .footer-inner { grid-template-columns: 1fr; gap: 2rem; }
  .footer-bottom { flex-direction: column; gap: 0.5rem; text-align: center; }
  .mock-float { display: none; }
}
</style>
