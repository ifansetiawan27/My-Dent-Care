<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuth } from '@/core/auth/useAuth'
import logoImg from '@/assets/logo-anim.png'
import logoAnimImg from '@/assets/logo-anim.png'

const router = useRouter()
const { isAuthenticated } = useAuth()

function goToApp() {
  router.push(isAuthenticated() ? '/dashboard' : '/login')
}

function goToBooking() {
  router.push('/login')
}

function scrollTo(id: string) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

/* ===== LAYANAN (dari PRD §3 — Domain Models) ===== */
const services = [
  {
    icon: 'tooth',
    title: 'Rekam Medis Digital',
    desc: 'Rekam medis pasien, odontogram, riwayat perawatan, resep, dan foto X-ray tersimpan digital & terpusat — aman dan mudah diakses.',
  },
  {
    icon: 'calendar',
    title: 'Penjadwalan & Appointment',
    desc: 'Manajemen jadwal dokter dengan anti-double-booking. Notifikasi SMS/Email/WhatsApp untuk kurangi no-show pasien.',
  },
  {
    icon: 'layers',
    title: 'Odontogram & Treatment',
    desc: 'Odontogram digital untuk tooth charting lengkap dengan kode perawatan. Setiap treatment tercatat dan terkait dengan appointment.',
  },
  {
    icon: 'invoice',
    title: 'Billing & Invoice',
    desc: 'Invoice profesional, multi payment (cash, transfer, kartu, e-wallet), pembagian tagihan, pajak PPN & integrasi payment gateway Midtrans.',
  },
  {
    icon: 'box',
    title: 'Inventaris & Farmasi',
    desc: 'Stock level per cabang, reorder point otomatis, batch & expiry date tracking untuk obat, serta transfer stock antar cabang.',
  },
  {
    icon: 'users',
    title: 'Multi-Cabang & Multi-Tenant',
    desc: 'Kelola 1–100+ cabang dari satu platform. Data terisolasi per organisasi dengan Row Level Security (RLS) PostgreSQL.',
  },
  {
    icon: 'chart',
    title: 'Laporan & Analitik',
    desc: 'Dashboards real-time: pendapatan, kunjungan pasien, produktivitas dokter, hingga laporan keuangan lengkap siap atas nama.',
  },
  {
    icon: 'shield',
    title: 'Keamanan & Audit Trail',
    desc: 'Enkripsi data, audit trail seluruh aktivitas, soft delete, dan backup harian otomatis sesuai standar privasi medis (GDPR-ready).',
  },
  {
    icon: 'ai',
    title: 'AI Assistant',
    desc: 'AI-powered diagnosis assistance, risk alert, dan perkiraan no-show — siap membantu pekerjaan klinik Anda (Q3 2027).',
  },
  {
    icon: 'plus',
    title: 'Role-Based Access Control',
    desc: 'RBAC granular untuk super admin, admin, dokter, resepsionis, dan kasir — setiap peran punya hak akses yang tepat.',
  },
  {
    icon: 'unlimited',
    title: 'Tanpa Batasan',
    desc: 'Unlimited users, unlimited pasien, unlimited transaksi. Satu harga flat tanpa biaya tersembunyi.',
  },
  {
    icon: 'govt',
    title: 'SATUSEHAT & BPJS Ready',
    desc: 'API-first & enterprise-grade siap integrasi dengan SATUSEHAT, BPJS Kesehatan, dan payment gateway (roadmap 2027).',
  },
]

/* ===== FITUR HIGHLIGHT (hero-lite, untuk user yang cepat) ===== */
const highlights = [
  'Designed untuk 1 hingga 100+ cabang',
  'Multi-tenant dengan Row Level Security',
  'Unlimited user, pasien & transaksi',
  'Backup harian otomatis',
  '99.5% Uptime SLA',
  'Audit trail lengkap & traceable',
]

/* ===== PRICING (PRD §17) ===== */
const plan = {
  code: 'professional',
  name: 'My Dent Care',
  price: 299000,
  branches: 1,
  trialDays: 30,
}

const priceLabel = new Intl.NumberFormat('id-ID').format(plan.price)
const currentYear = new Date().getFullYear()

/* ===== TESTIMONI ===== */
const testimonials = [
  {
    name: 'drg. Sarah Amelia',
    clinic: 'Klinik Gigi Sehat · Jakarta',
    text: 'My Dent Care mengubah cara kami mengelola klinik. Registrasi pasien dan billing yang dulu memakan waktu berjam-jam kini selesai dalam hitungan menit. Pasien merasa dilayani lebih cepat.',
    avatar: 'SA',
    rating: 5,
  },
  {
    name: 'drg. Budi Santoso',
    clinic: 'Dental Care Plus · Surabaya',
    text: 'Rekam medis digital dan odontogram terintegrasi membuat diagnosis lebih akurat. Sistem mengingatkan kami soal jadwal follow-up — pasien pun makin loyal.',
    avatar: 'BS',
    rating: 5,
  },
  {
    name: 'drg. Rina Kusuma',
    clinic: 'Smile Dental Clinic · Bandung',
    text: 'Laporan keuangan yang detail membantu saya mengambil keputusan bisnis dengan percaya diri. Dengan 3 cabang, semua data konsolidasi dalam satu dashboard.',
    avatar: 'RK',
    rating: 5,
  },
]

/* ===== STATS ===== */
const stats = [
  { value: '99.5%', label: 'Uptime SLA' },
  { value: '<200ms', label: 'Respons API (p95)' },
  { value: '100+', label: 'Cabang per Organisasi' },
  { value: '30 Hari', label: 'Free Trial Penuh' },
]

/* ===== STEP ===== */
const steps = [
  { num: '01', title: 'Buat Akun Gratis', desc: 'Daftar dalam 2 menit dengan trial 30 hari penuh — tanpa kartu kredit, tanpa komitmen.' },
  { num: '02', title: 'Setup Profil & Tim', desc: 'Masukkan profil klinik, cabang, dan tim dokter Anda. Konfigurasi hak akses per peran.' },
  { num: '03', title: 'Import & Jalankan', desc: 'Impor data pasien dari sistem lama atau mulai dari nol. Klinik Anda siap beroperasi.' },
]

function ratingStars(r: number): number[] {
  return Array.from({ length: r }, (_, i) => i)
}
</script>

<template>
  <div class="lp">

    <!-- ============ NAVBAR ============ -->
    <header class="lp-nav">
      <nav class="lp-nav-inner" aria-label="Navigasi utama">
        <a href="#" class="lp-brand" @click.prevent="scrollTo('top')">
          <span class="lp-brand-mark">
            <img :src="logoImg" alt="My Dent Care logo" class="lp-brand-logo" />
          </span>
          <span class="lp-brand-text">
            <span class="lp-brand-name">My Dent Care</span>
            <span class="lp-brand-tag">Dental Clinic ERP</span>
          </span>
        </a>
        <ul class="lp-nav-links">
          <li><a href="#layanan" class="lp-nav-link" @click.prevent="scrollTo('layanan')">Layanan</a></li>
          <li><a href="#cara-kerja" class="lp-nav-link" @click.prevent="scrollTo('cara-kerja')">Cara Kerja</a></li>
          <li><a href="#testimoni" class="lp-nav-link" @click.prevent="scrollTo('testimoni')">Testimoni</a></li>
          <li><a href="#harga" class="lp-nav-link" @click.prevent="scrollTo('harga')">Harga</a></li>
        </ul>
        <div class="lp-nav-actions">
          <button class="lp-btn lp-btn-ghost" @click="router.push('/login')">Masuk</button>
          <button class="lp-btn lp-btn-solid" @click="goToApp">Coba Gratis</button>
        </div>
      </nav>
    </header>

    <main id="top">
      <!-- ============ HERO ============ -->
      <section class="lp-hero" aria-label="Beranda">
        <div class="lp-hero-bg" aria-hidden="true">
          <span class="lp-hero-blob lp-hero-blob-1"></span>
          <span class="lp-hero-blob lp-hero-blob-2"></span>
          <span class="lp-hero-grid" aria-hidden="true"></span>
        </div>
        <div class="lp-hero-inner">
          <div class="lp-hero-copy">
            <div class="lp-hero-badge">
              <span class="lp-hero-badge-dot"></span>
              <span>SATUSEHAT &amp; BPJS Ready</span>
            </div>
            <h1 class="lp-hero-title">
              Klinik Gigi Anda,<br />
              <span class="lp-hero-accent">Satu Platform</span><br />
              Sejak Cabang Pertama
            </h1>
            <p class="lp-hero-desc">
              My Dent Care menghubungkan rekam medis, odontogram, penjadwalan, inventaris, billing,
              dan laporan keuangan dalam satu ERP <em>enterprise-grade</em> — dibangun untuk klinik
              dari 1 cabang hingga jaringan 100+ cabang.
            </p>
            <div class="lp-hero-cta">
              <button class="lp-btn lp-btn-primary lp-btn-lg" @click="goToApp">
                Mulai Free Trial {{ plan.trialDays }} Hari
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </button>
              <button class="lp-btn lp-btn-secondary lp-btn-lg" @click="goToBooking">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Book Appointment Demo
              </button>
            </div>
            <p class="lp-hero-note">
              Tanpa kartu kredit &middot; Semua fitur terbuka &middot; Batalkan kapan saja
            </p>
            <ul class="lp-hero-trust">
              <li v-for="h in highlights" :key="h">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ h }}
              </li>
            </ul>
          </div>

          <div class="lp-hero-visual" aria-hidden="true">
            <!-- Animated logo -->
            <div class="lp-hero-logo-stage">
              <span class="lp-hero-logo-glow"></span>
              <span class="lp-hero-logo-ring r1"></span>
              <span class="lp-hero-logo-ring r2"></span>
              <img :src="logoAnimImg" alt="" class="lp-hero-logo-img" />
            </div>
            <div class="lp-mock lp-mock-main">
              <div class="lp-mock-head">
                <span class="lp-mock-dot d-red"></span>
                <span class="lp-mock-dot d-yellow"></span>
                <span class="lp-mock-dot d-green"></span>
                <span class="lp-mock-title">Dashboard · My Dent Care</span>
              </div>
              <div class="lp-mock-body">
                <div class="lp-mock-stats">
                  <div class="lp-mock-stat">
                    <span class="lp-mock-stat-val blue">48</span>
                    <span class="lp-mock-stat-label">Pasien Hari Ini</span>
                  </div>
                  <div class="lp-mock-stat">
                    <span class="lp-mock-stat-val teal">12</span>
                    <span class="lp-mock-stat-label">Appointment</span>
                  </div>
                  <div class="lp-mock-stat">
                    <span class="lp-mock-stat-val green">Rp 4,2jt</span>
                    <span class="lp-mock-stat-label">Pendapatan</span>
                  </div>
                </div>
                <div class="lp-mock-chart">
                  <div class="lp-mock-chart-label">Kunjungan Minggu Ini</div>
                  <div class="lp-mock-bars">
                    <div class="lp-mock-bar" style="height: 40%"></div>
                    <div class="lp-mock-bar" style="height: 65%"></div>
                    <div class="lp-mock-bar" style="height: 55%"></div>
                    <div class="lp-mock-bar" style="height: 80%"></div>
                    <div class="lp-mock-bar active" style="height: 92%"></div>
                    <div class="lp-mock-bar" style="height: 70%"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="lp-mock-float f1">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" class="ok" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <span class="lp-mock-float-title">Rekam Medis Tersimpan</span>
                <span class="lp-mock-float-sub">Pasien: Andi W. · baru saja</span>
              </div>
            </div>
            <div class="lp-mock-float f2">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" class="trend" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
              <div>
                <span class="lp-mock-float-title">+12% Pendapatan</span>
                <span class="lp-mock-float-sub">Dibanding bulan lalu</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ============ STATS ============ -->
      <section class="lp-stats" aria-label="Statistik platform">
        <div class="lp-stats-inner">
          <div v-for="s in stats" :key="s.label" class="lp-stat">
            <span class="lp-stat-value">{{ s.value }}</span>
            <span class="lp-stat-label">{{ s.label }}</span>
          </div>
        </div>
      </section>

      <!-- ============ LAYANAN ============ -->
      <section id="layanan" class="lp-section lp-section-white">
        <div class="lp-container">
          <div class="lp-section-head">
            <span class="lp-kicker">Keunggulan Fitur</span>
            <h2 class="lp-section-title">Semua Kebutuhan Operasional Klinik Gigi <span class="lp-accent">dalam Satu Sistem</span></h2>
            <p class="lp-section-desc">
              Dibangun dari PRD &amp; roadmap enterprise platform — setiap modul dirancang untuk
              klinik single-location sampai jaringan multibranch.
            </p>
          </div>
          <div class="lp-services">
            <article v-for="s in services" :key="s.title" class="lp-service">
              <div class="lp-service-icon">
                <svg v-if="s.icon === 'tooth'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <svg v-else-if="s.icon === 'calendar'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <svg v-else-if="s.icon === 'layers'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7l8-4 8 4-8 4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4" />
                </svg>
                <svg v-else-if="s.icon === 'box'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <svg v-else-if="s.icon === 'ai'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <svg v-else-if="s.icon === 'govt'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9-4 9 4-9 4-9-4zM3 10l9 4 9-4M3 14l9 4 9-4M21 6v12" />
                </svg>
                <svg v-else-if="s.icon === 'unlimited'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <svg v-else-if="s.icon === 'plus'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                <!-- default: users / invoice / chart / shield -->
                <svg v-else-if="s.icon === 'users'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <svg v-else-if="s.icon === 'invoice'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                </svg>
                <svg v-else-if="s.icon === 'chart'" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h3 class="lp-service-title">{{ s.title }}</h3>
              <p class="lp-service-desc">{{ s.desc }}</p>
            </article>
          </div>
        </div>
      </section>

      <!-- ============ CARA KERJA ============ -->
      <section id="cara-kerja" class="lp-section lp-section-tint">
        <div class="lp-container">
          <div class="lp-section-head">
            <span class="lp-kicker">Cara Kerja</span>
            <h2 class="lp-section-title">Mulai Hanya dalam <span class="lp-accent">3 Langkah</span></h2>
            <p class="lp-section-desc">Dari pendaftaran sampai klinik beroperasi penuh — tanpa implementasi rumit.</p>
          </div>
          <div class="lp-steps">
            <div v-for="(step, i) in steps" :key="step.num" class="lp-step">
              <div class="lp-step-num">{{ step.num }}</div>
              <div v-if="i < steps.length - 1" class="lp-step-line" aria-hidden="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="26" height="26">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
              <h3 class="lp-step-title">{{ step.title }}</h3>
              <p class="lp-step-desc">{{ step.desc }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ============ TESTIMONI ============ -->
      <section id="testimoni" class="lp-section lp-section-white">
        <div class="lp-container">
          <div class="lp-section-head">
            <span class="lp-kicker">Testimoni Pasien &amp; Klinik</span>
            <h2 class="lp-section-title">Dipercaya Dokter Gigi <span class="lp-accent">di Seluruh Indonesia</span></h2>
            <p class="lp-section-desc">Pengalaman nyata dari klinik yang beroperasi lebih efisien bersama My Dent Care.</p>
          </div>
          <div class="lp-testis">
            <article v-for="t in testimonials" :key="t.name" class="lp-testi">
              <div class="lp-testi-stars" :aria-label="`Rating ${t.rating} dari 5`">
                <svg v-for="star in ratingStars(t.rating)" :key="star" fill="currentColor" viewBox="0 0 20 20" width="16" height="16">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <p class="lp-testi-text">&ldquo;{{ t.text }}&rdquo;</p>
              <div class="lp-testi-author">
                <span class="lp-testi-avatar">{{ t.avatar }}</span>
                <span class="lp-testi-meta">
                  <strong class="lp-testi-name">{{ t.name }}</strong>
                  <span class="lp-testi-clinic">{{ t.clinic }}</span>
                </span>
              </div>
            </article>
          </div>
        </div>
      </section>

      <!-- ============ HARGA ============ -->
      <section id="harga" class="lp-section lp-section-tint">
        <div class="lp-container">
          <div class="lp-section-head">
            <span class="lp-kicker">Harga</span>
            <h2 class="lp-section-title">Satu Harga Transparan, <span class="lp-accent">Semua Fitur</span></h2>
            <p class="lp-section-desc">Rp {{ priceLabel }} per cabang per bulan — tanpa biaya setup, tanpa biaya tersembunyi.</p>
          </div>

          <div class="lp-price-card">
            <div class="lp-price-side">
              <span class="lp-price-badge">FREE TRIAL {{ plan.trialDays }} HARI</span>
              <h3 class="lp-price-name">{{ plan.name }}</h3>
              <div class="lp-price-amount">
                <span class="lp-price-cur">Rp</span>
                <span class="lp-price-num">{{ priceLabel }}</span>
                <span class="lp-price-per">/bulan<br /><small>per cabang</small></span>
              </div>
              <p class="lp-price-note">Setelah trial berakhir. Batalkan kapan saja — data tetap aman.</p>
              <button class="lp-btn lp-btn-primary lp-btn-lg" @click="goToApp">
                Mulai Trial Gratis {{ plan.trialDays }} Hari
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </button>
            </div>
            <div class="lp-price-divider" aria-hidden="true"></div>
            <div class="lp-price-includes">
              <p class="lp-price-includes-title">Semua sudah termasuk:</p>
              <ul class="lp-price-features">
                <li>
                  <svg class="lp-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span>Full features, tanpa fitur gating</span>
                </li>
                <li>
                  <svg class="lp-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span>Unlimited users, pasien &amp; transaksi</span>
                </li>
                <li>
                  <svg class="lp-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span>Rekam medis, odontogram &amp; EMR digital</span>
                </li>
                <li>
                  <svg class="lp-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span>Billing, invoice &amp; laporan keuangan otomatis</span>
                </li>
                <li>
                  <svg class="lp-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span>Backup harian otomatis &amp; pemulihan data</span>
                </li>
                <li>
                  <svg class="lp-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span>Email support, dokumentasi &amp; SLA 99,5%</span>
                </li>
                <li>
                  <svg class="lp-check" fill="currentColor" viewBox="0 0 20 20" width="18" height="18"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                  <span>Integrasi SATUSEHAT, BPJS &amp; Midtrans (roadmap)</span>
                </li>
              </ul>
              <p class="lp-price-billing">
                Contoh: 1 cabang = Rp {{ priceLabel }}/bulan · 3 cabang = Rp {{ new Intl.NumberFormat('id-ID').format(plan.price * 3) }}/bulan
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- ============ CTA ============ -->
      <section class="lp-cta" aria-label="Ajakan mencoba">
        <div class="lp-cta-inner">
          <h2 class="lp-cta-title">Siap Bawa Klinik Anda ke Era Digital?</h2>
          <p class="lp-cta-desc">
            Coba semua fitur selama {{ plan.trialDays }} hari tanpa kartu kredit.
            Setelah trial, hanya Rp {{ priceLabel }} per cabang per bulan.
          </p>
          <div class="lp-cta-actions">
            <button class="lp-btn lp-btn-white lp-btn-lg" @click="goToApp">
              Mulai Gratis Sekarang
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </button>
            <a href="mailto:support@mydentcare.com" class="lp-btn lp-btn-outline-white lp-btn-lg">Hubungi Tim Kami</a>
          </div>
        </div>
      </section>
    </main>

    <!-- ============ FOOTER ============ -->
    <footer class="lp-footer">
      <div class="lp-container">
        <div class="lp-footer-grid">
          <div class="lp-footer-brand">
            <a href="#" class="lp-brand" @click.prevent="scrollTo('top')">
              <span class="lp-brand-mark">
                <img :src="logoImg" alt="My Dent Care logo" class="lp-brand-logo lp-brand-logo-invert" />
              </span>
              <span class="lp-brand-text">
                <span class="lp-brand-name light">My Dent Care</span>
                <span class="lp-brand-tag">Dental Clinic ERP</span>
              </span>
            </a>
            <p class="lp-footer-desc">Enterprise dental clinic ERP untuk Indonesia — dari 1 cabang hingga 100+ cabang. Built with Laravel 12, PostgreSQL &amp; solid DDD architecture.</p>
          </div>
          <nav class="lp-footer-col" aria-label="Navigasi produk">
            <h4 class="lp-footer-title">Produk</h4>
            <a href="#layanan" @click.prevent="scrollTo('layanan')">Layanan</a>
            <a href="#cara-kerja" @click.prevent="scrollTo('cara-kerja')">Cara Kerja</a>
            <a href="#harga" @click.prevent="scrollTo('harga')">Harga</a>
            <router-link to="/login">Masuk</router-link>
          </nav>
          <nav class="lp-footer-col" aria-label="Navigasi perusahaan">
            <h4 class="lp-footer-title">Perusahaan</h4>
            <a href="#testimoni" @click.prevent="scrollTo('testimoni')">Testimoni</a>
            <a href="#top" @click.prevent="scrollTo('top')">Beranda</a>
            <a href="mailto:enterprise@mydentcare.com">Enterprise</a>
          </nav>
          <nav class="lp-footer-col" aria-label="Kontak support">
            <h4 class="lp-footer-title">Support</h4>
            <a href="mailto:support@mydentcare.com">support@mydentcare.com</a>
            <a href="mailto:enterprise@mydentcare.com">enterprise@mydentcare.com</a>
            <a href="#cara-kerja" @click.prevent="scrollTo('cara-kerja')">Dokumentasi</a>
          </nav>
        </div>
        <div class="lp-footer-bottom">
          <span>&copy; {{ currentYear }} My Dent Care. All rights reserved.</span>
          <span>Dibuat untuk klinik gigi di Indonesia &middot; SATUSEHAT &amp; BPJS ready</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* =============================================================
   BASE
   ============================================================= */
.lp {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  color: #111827;
  background: #ffffff;
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}
/* fix logo-text-wrap inside brand reuse in footer to not inherit old styles */
.lp-brand-text { display: flex; flex-direction: column; line-height: 1.15; }
.lp-accent {
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* =============================================================
   NAVBAR
   ============================================================= */
.lp-nav {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(229, 231, 235, 0.8);
}
.lp-nav-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}
.lp-brand {
  display: inline-flex;
  align-items: center;
  gap: 0.625rem;
  text-decoration: none;
}
.lp-brand-mark {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e0f2fe, #ccfbf1);
  border: 1px solid #bae6fd;
  flex-shrink: 0;
}
.lp-brand-logo {
  height: 38px;
  width: auto;
  object-fit: contain;
  display: block;
}
.lp-brand-name {
  font-size: 1.05rem;
  font-weight: 800;
  background: linear-gradient(135deg, #0ea5e9, #0d9488);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
.lp-brand-name.light {
  background: none;
  -webkit-text-fill-color: #ffffff;
  color: #ffffff;
}
.lp-brand-tag {
  font-size: 0.6875rem;
  color: #94a3b8;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}
.lp-nav-links {
  display: flex;
  gap: 2rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
.lp-nav-link {
  color: #4b5563;
  text-decoration: none;
  font-size: 0.9375rem;
  font-weight: 500;
  transition: color 0.2s;
}
.lp-nav-link:hover { color: #0ea5e9; }
.lp-nav-actions { display: flex; gap: 0.625rem; }

/* =============================================================
   BUTTONS
   ============================================================= */
.lp-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9375rem;
  padding: 0.625rem 1.25rem;
  border-radius: 10px;
  transition: all 0.2s ease;
  text-decoration: none;
  font-family: inherit;
}
.lp-btn-lg { padding: 0.9375rem 2rem; font-size: 1rem; border-radius: 12px; }
.lp-btn-ghost {
  background: transparent;
  color: #0ea5e9;
  border: 1.5px solid #bae6fd;
}
.lp-btn-ghost:hover { background: #f0f9ff; }
.lp-btn-solid {
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: #ffffff;
  box-shadow: 0 6px 18px rgba(14, 165, 233, 0.3);
}
.lp-btn-solid:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(14, 165, 233, 0.4); }
.lp-btn-primary {
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: #ffffff;
  box-shadow: 0 10px 28px rgba(14, 165, 233, 0.35);
}
.lp-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(14, 165, 233, 0.45); }
.lp-btn-secondary {
  background: #ffffff;
  color: #374151;
  border: 1.5px solid #e5e7eb;
}
.lp-btn-secondary:hover { border-color: #0ea5e9; color: #0ea5e9; background: #f0f9ff; }
.lp-btn-white {
  background: #ffffff;
  color: #0ea5e9;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.15);
}
.lp-btn-white:hover { transform: translateY(-2px); box-shadow: 0 14px 36px rgba(0, 0, 0, 0.2); }
.lp-btn-outline-white {
  background: transparent;
  color: #ffffff;
  border: 2px solid rgba(255, 255, 255, 0.65);
}
.lp-btn-outline-white:hover { background: rgba(255, 255, 255, 0.14); border-color: #fff; }

/* =============================================================
   HERO
   ============================================================= */
.lp-hero {
  position: relative;
  background: linear-gradient(180deg, #f8fbfe 0%, #ffffff 100%);
  min-height: 620px;
  display: flex;
  align-items: center;
  overflow: hidden;
}
.lp-hero-bg { position: absolute; inset: 0; overflow: hidden; }
.lp-hero-blob { position: absolute; border-radius: 50%; filter: blur(90px); }
.lp-hero-blob-1 { width: 480px; height: 480px; background: rgba(14, 165, 233, 0.14); top: -120px; right: -80px; }
.lp-hero-blob-2 { width: 380px; height: 380px; background: rgba(20, 184, 166, 0.12); bottom: -100px; left: -60px; }
.lp-hero-grid {
  position: absolute;
  inset: 0;
  opacity: 0.5;
  background-image: radial-gradient(rgba(14, 165, 233, 0.12) 1px, transparent 1px);
  background-size: 26px 26px;
  -webkit-mask-image: radial-gradient(ellipse at 70% 30%, black, transparent 70%);
  mask-image: radial-gradient(ellipse at 70% 30%, black, transparent 70%);
}
.lp-hero-inner {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 5rem 1.5rem;
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  gap: 3rem;
  align-items: center;
  position: relative;
  z-index: 2;
}
.lp-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #e0f2fe, #ccfbf1);
  color: #0369a1;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.8125rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  border: 1px solid #bae6fd;
}
.lp-hero-badge-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #0ea5e9;
  animation: lpPulse 2s ease-in-out infinite;
}
@keyframes lpPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }
.lp-hero-title {
  font-size: 3.25rem;
  font-weight: 800;
  line-height: 1.08;
  letter-spacing: -0.025em;
  color: #0f172a;
  margin: 0 0 1.25rem;
}
.lp-hero-desc {
  font-size: 1.125rem;
  line-height: 1.7;
  color: #475569;
  margin: 0 0 2rem;
  max-width: 34rem;
}
.lp-hero-cta { display: flex; gap: 0.875rem; flex-wrap: wrap; margin-bottom: 1rem; }
.lp-hero-note { font-size: 0.8125rem; color: #94a3b8; margin: 0 0 1.75rem; }
.lp-hero-trust {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem 1.25rem;
}
.lp-hero-trust li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #475569;
}
.lp-hero-trust svg { color: #10b981; flex-shrink: 0; }

/* Hero visual */
.lp-hero-visual { position: relative; }
.lp-hero-logo-stage {
  position: absolute;
  top: -64px;
  right: 8px;
  width: 118px;
  height: 118px;
  z-index: 4;
  display: flex;
  align-items: center;
  justify-content: center;
}
.lp-hero-logo-img {
  position: relative;
  z-index: 2;
  width: 92px;
  height: 92px;
  object-fit: contain;
  filter: drop-shadow(0 10px 22px rgba(14, 165, 233, 0.35));
  animation: lpFloat 6s ease-in-out infinite;
}
.lp-hero-logo-glow {
  position: absolute;
  inset: 4px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(14, 165, 233, 0.3), rgba(20, 184, 166, 0) 70%);
  filter: blur(12px);
  animation: lpGlow 4s ease-in-out infinite;
}
.lp-hero-logo-ring {
  position: absolute;
  border-radius: 50%;
  border: 2px solid transparent;
  pointer-events: none;
}
.lp-hero-logo-ring.r1 {
  inset: 0;
  border-top-color: rgba(14, 165, 233, 0.55);
  border-right-color: rgba(20, 184, 166, 0.3);
  animation: lpSpin 9s linear infinite;
}
.lp-hero-logo-ring.r2 {
  inset: 10px;
  border-bottom-color: rgba(236, 72, 153, 0.35);
  border-left-color: rgba(14, 165, 233, 0.3);
  animation: lpSpin 6s linear reverse infinite;
}
@keyframes lpSpin { to { transform: rotate(360deg); } }
@keyframes lpFloat { 0%, 100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-9px) rotate(-2deg); } }
@keyframes lpGlow { 0%, 100% { opacity: 0.5; transform: scale(0.94); } 50% { opacity: 1; transform: scale(1.08); } }

.lp-mock {
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 30px 80px rgba(15, 23, 42, 0.16);
  border: 1px solid #e5e7eb;
  overflow: hidden;
}
.lp-mock-head {
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.lp-mock-dot { width: 11px; height: 11px; border-radius: 50%; }
.d-red { background: #fca5a5; }
.d-yellow { background: #fde68a; }
.d-green { background: #6ee7b7; }
.lp-mock-title { font-size: 0.75rem; color: #94a3b8; margin-left: 0.5rem; }
.lp-mock-body { padding: 1.25rem; }
.lp-mock-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
.lp-mock-stat {
  background: #f8fafc;
  border-radius: 10px;
  padding: 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  text-align: center;
}
.lp-mock-stat-val { font-size: 1.25rem; font-weight: 800; line-height: 1; }
.lp-mock-stat-val.blue { color: #0ea5e9; }
.lp-mock-stat-val.teal { color: #0d9488; }
.lp-mock-stat-val.green { color: #10b981; }
.lp-mock-stat-label { font-size: 0.6875rem; color: #94a3b8; }
.lp-mock-chart-label { font-size: 0.75rem; color: #64748b; font-weight: 600; margin-bottom: 0.5rem; }
.lp-mock-bars { display: flex; align-items: flex-end; gap: 6px; height: 64px; }
.lp-mock-bar { flex: 1; background: #e5e7eb; border-radius: 4px 4px 0 0; }
.lp-mock-bar.active { background: linear-gradient(to top, #0ea5e9, #14b8a6); }
.lp-mock-float {
  position: absolute;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 0.8125rem;
  min-width: 190px;
}
.lp-mock-float .ok { color: #10b981; flex-shrink: 0; }
.lp-mock-float .trend { color: #0d9488; flex-shrink: 0; }
.lp-mock-float-title { font-weight: 700; color: #0f172a; display: block; }
.lp-mock-float-sub { font-size: 0.6875rem; color: #94a3b8; display: block; }
.lp-mock-float.f1 { bottom: -22px; left: -30px; }
.lp-mock-float.f2 { top: 28px; right: -34px; }

/* =============================================================
   STATS
   ============================================================= */
.lp-stats {
  background: linear-gradient(135deg, #0891b2 0%, #0d9488 100%);
  background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
  padding: 2.75rem 1.5rem;
}
.lp-stats-inner {
  max-width: 1000px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  text-align: center;
}
.lp-stat { display: flex; flex-direction: column; gap: 0.375rem; }
.lp-stat-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
  letter-spacing: -0.02em;
}
.lp-stat-label { font-size: 0.9375rem; color: rgba(255, 255, 255, 0.85); font-weight: 500; }

/* =============================================================
   SECTIONS
   ============================================================= */
.lp-section { padding: 5.5rem 1.5rem; }
.lp-section-white { background: #ffffff; }
.lp-section-tint { background: #f6fafc; }
.lp-container { max-width: 1200px; margin: 0 auto; }
.lp-section-head { text-align: center; max-width: 760px; margin: 0 auto 3.5rem; }
.lp-kicker {
  display: inline-block;
  background: linear-gradient(135deg, #e0f2fe, #ccfbf1);
  color: #0369a1;
  padding: 0.325rem 0.875rem;
  border-radius: 999px;
  font-size: 0.8125rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 1rem;
}
.lp-section-title {
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1.15;
  color: #0f172a;
  letter-spacing: -0.02em;
  margin: 0 0 1rem;
}
.lp-section-desc { font-size: 1.0625rem; color: #64748b; line-height: 1.7; margin: 0; }

/* =============================================================
   SERVICES
   ============================================================= */
.lp-services {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}
.lp-service {
  background: #ffffff;
  border: 1px solid #eef2f7;
  border-radius: 16px;
  padding: 1.75rem;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}
.lp-service::before {
  content: '';
  position: absolute;
  inset: 0 0 auto 0;
  height: 3px;
  background: linear-gradient(90deg, #0ea5e9, #14b8a6);
  opacity: 0;
  transition: opacity 0.3s ease;
}
.lp-service:hover {
  transform: translateY(-5px);
  box-shadow: 0 18px 44px rgba(14, 165, 233, 0.14);
  border-color: #d6ecfb;
}
.lp-service:hover::before { opacity: 1; }
.lp-service-icon {
  width: 52px;
  height: 52px;
  border-radius: 13px;
  background: linear-gradient(135deg, #e0f2fe, #ccfbf1);
  color: #0284c7;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.1rem;
}
.lp-service-title { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem; }
.lp-service-desc { font-size: 0.9375rem; color: #64748b; line-height: 1.65; margin: 0; }

/* =============================================================
   STEPS
   ============================================================= */
.lp-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; align-items: start; }
.lp-step { text-align: center; position: relative; padding: 1.5rem 1rem; }
.lp-step-num {
  width: 68px; height: 68px;
  margin: 0 auto 1.25rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: #fff;
  font-size: 1.375rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 24px rgba(14, 165, 233, 0.35);
}
.lp-step-line {
  position: absolute;
  top: 50px;
  right: -14%;
  color: #c7d4e0;
  z-index: 1;
}
.lp-step-title { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem; }
.lp-step-desc { font-size: 0.9375rem; color: #64748b; line-height: 1.65; margin: 0 auto; max-width: 30rem; }

/* =============================================================
   TESTIMONIALS
   ============================================================= */
.lp-testis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.lp-testi {
  background: #f8fafc;
  border: 1px solid #eef2f7;
  border-radius: 16px;
  padding: 1.75rem;
  display: flex;
  flex-direction: column;
  transition: all 0.3s;
}
.lp-testi:hover { background: #fff; box-shadow: 0 16px 40px rgba(14, 165, 233, 0.12); border-color: #d6ecfb; }
.lp-testi-stars { color: #f59e0b; display: flex; gap: 2px; margin-bottom: 0.875rem; }
.lp-testi-text { font-size: 0.9375rem; color: #374151; line-height: 1.7; font-style: italic; margin: 0 0 1.5rem; flex: 1; }
.lp-testi-author { display: flex; align-items: center; gap: 0.75rem; }
.lp-testi-avatar {
  width: 46px; height: 46px; border-radius: 12px;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: #fff; font-weight: 800; font-size: 0.875rem;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.lp-testi-meta { display: flex; flex-direction: column; }
.lp-testi-name { font-size: 0.9375rem; color: #0f172a; }
.lp-testi-clinic { font-size: 0.8125rem; color: #94a3b8; }

/* =============================================================
   PRICING
   ============================================================= */
.lp-price-card {
  max-width: 980px;
  margin: 0 auto;
  background: #ffffff;
  border: 2px solid #bae6fd;
  border-radius: 24px;
  box-shadow: 0 24px 70px rgba(14, 165, 233, 0.16);
  display: flex;
  overflow: hidden;
}
.lp-price-side {
  flex: 0 0 320px;
  background: linear-gradient(160deg, #0284c7 0%, #0d9488 100%);
  padding: 2.5rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}
.lp-price-badge {
  align-self: flex-start;
  background: rgba(255, 255, 255, 0.22);
  border: 1px solid rgba(255, 255, 255, 0.45);
  color: #fff;
  font-size: 0.6875rem;
  font-weight: 800;
  letter-spacing: 0.07em;
  padding: 4px 14px;
  border-radius: 999px;
}
.lp-price-name { font-size: 1.625rem; font-weight: 800; color: #fff; margin: 0; line-height: 1.1; }
.lp-price-amount { display: flex; align-items: flex-end; gap: 6px; margin: 0.5rem 0; }
.lp-price-cur { font-size: 1.125rem; font-weight: 700; color: rgba(255, 255, 255, 0.85); padding-bottom: 8px; }
.lp-price-num { font-size: 3.75rem; font-weight: 900; color: #fff; line-height: 1; }
.lp-price-per { font-size: 1rem; color: rgba(255, 255, 255, 0.9); font-weight: 600; padding-bottom: 8px; }
.lp-price-per small { font-size: 0.75rem; font-weight: 500; opacity: 0.8; display: block; }
.lp-price-note { font-size: 0.8125rem; color: rgba(255, 255, 255, 0.75); line-height: 1.45; margin: 0; }
.lp-price-side .lp-btn { margin-top: 0.875rem; background: #fff; color: #0284c7; }
.lp-price-side .lp-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(0, 0, 0, 0.18); }
.lp-price-divider { width: 1px; background: #e5e7eb; flex-shrink: 0; }
.lp-price-includes { flex: 1; padding: 2.5rem 2rem; }
.lp-price-includes-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 1.5rem;
}
.lp-price-features {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem 1.5rem;
}
.lp-price-features li { display: flex; align-items: flex-start; gap: 0.625rem; font-size: 0.9375rem; color: #334155; line-height: 1.4; }
.lp-check { color: #10b981; flex-shrink: 0; margin-top: 1px; }
.lp-price-billing {
  margin: 1.75rem 0 0;
  padding: 0.875rem 1rem;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 10px;
  font-size: 0.875rem;
  color: #0369a1;
}

/* =============================================================
   CTA
   ============================================================= */
.lp-cta {
  background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
  padding: 5.5rem 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.lp-cta::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255, 255, 255, 0.14) 1px, transparent 1px);
  background-size: 24px 24px;
  opacity: 0.4;
}
.lp-cta-inner { max-width: 720px; margin: 0 auto; position: relative; z-index: 1; }
.lp-cta-title {
  font-size: 2.75rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.02em;
  line-height: 1.15;
  margin: 0 0 1rem;
}
.lp-cta-desc {
  font-size: 1.0625rem;
  color: rgba(255, 255, 255, 0.88);
  line-height: 1.65;
  margin: 0 0 2.25rem;
}
.lp-cta-actions { display: flex; gap: 0.875rem; justify-content: center; flex-wrap: wrap; }

/* =============================================================
   FOOTER
   ============================================================= */
.lp-footer { background: #0b1220; color: #94a3b8; padding: 4rem 1.5rem 0; }
.lp-footer-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2.2fr 1fr 1fr 1.2fr;
  gap: 3rem;
  padding-bottom: 3rem;
  border-bottom: 1px solid #1e293b;
}
.lp-footer-brand img.lp-brand-logo-invert { filter: brightness(1.15) drop-shadow(0 0 8px rgba(14, 165, 233, 0.4)); }
.lp-footer-desc { font-size: 0.9375rem; line-height: 1.7; color: #64748b; margin: 1rem 0 0; max-width: 320px; }
.lp-footer-col { display: flex; flex-direction: column; gap: 0.625rem; }
.lp-footer-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 0.25rem;
}
.lp-footer-col a {
  color: #94a3b8;
  text-decoration: none;
  font-size: 0.9375rem;
  transition: color 0.2s;
}
.lp-footer-col a:hover { color: #38bdf8; }
.lp-footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem 0;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  font-size: 0.8125rem;
  color: #475569;
  flex-wrap: wrap;
}

/* =============================================================
   RESPONSIVE
   ============================================================= */
@media (max-width: 1024px) {
  .lp-hero-inner { grid-template-columns: 1fr; gap: 3.5rem; }
  .lp-hero-visual { max-width: 520px; margin: 0 auto; width: 100%; }
  .lp-hero-title { font-size: 2.75rem; }
  .lp-services { grid-template-columns: repeat(2, 1fr); }
  .lp-testis { grid-template-columns: repeat(2, 1fr); }
  .lp-steps { grid-template-columns: 1fr; }
  .lp-step-line { display: none; }
  .lp-stats-inner { grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
  .lp-price-card { flex-direction: column; }
  .lp-price-divider { width: 100%; height: 1px; }
  .lp-footer-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
  .lp-nav-links { display: none; }
  .lp-nav-inner { height: 64px; }
  .lp-hero-title { font-size: 2.25rem; }
  .lp-section-title { font-size: 1.875rem; }
  .lp-services { grid-template-columns: 1fr; }
  .lp-testis { grid-template-columns: 1fr; }
  .lp-stats-inner { grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
  .lp-stat-value { font-size: 2rem; }
  .lp-price-side { padding: 2rem 1.5rem; }
  .lp-price-includes { padding: 2rem 1.5rem; }
  .lp-price-features { grid-template-columns: 1fr; }
  .lp-cta-title { font-size: 2.125rem; }
  .lp-footer-grid { grid-template-columns: 1fr; gap: 2rem; }
  .lp-footer-bottom { flex-direction: column; text-align: center; }
  .lp-mock-float { display: none; }
  .lp-hero-logo-stage { top: -52px; right: 4px; width: 96px; height: 96px; }
  .lp-hero-logo-img { width: 76px; height: 76px; }
}
@media (prefers-reduced-motion: reduce) {
  .lp-hero-logo-img,
  .lp-hero-logo-glow,
  .lp-hero-logo-ring,
  .lp-hero-badge-dot {
    animation: none !important;
  }
}
</style>