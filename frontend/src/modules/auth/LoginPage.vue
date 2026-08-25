<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '@/core/auth/useAuth'
import { useRouter, useRoute } from 'vue-router'
import logoImg from '@/assets/logo-anim.png'

const { login, loading, error: authError } = useAuth()
const router = useRouter()
const route = useRoute()
const email = ref('')
const password = ref('')
const localError = ref<string | null>(null)

// Demo accounts dari DemoSeeder
const demoAccounts = [
  { label: 'Super Admin', email: 'superadmin@demodental.com', password: 'password123', role: 'Superadmin' },
  { label: 'Dokter', email: 'drjane@demodental.com', password: 'password123', role: 'drg. Jane Smith' },
  { label: 'Resepsionis', email: 'sarah@demodental.com', password: 'password123', role: 'Sarah' },
]

function fillDemo(account: typeof demoAccounts[0]): void {
  email.value = account.email
  password.value = account.password
  localError.value = null
}

async function handleLogin(): Promise<void> {
  localError.value = null
  try {
    await login(email.value, password.value)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    router.push(redirect)
  } catch {
    localError.value = authError.value ?? 'Login gagal. Periksa email dan password Anda.'
  }
}
</script>

<template>
  <div class="login-bg">
    <div class="login-card">
      <!-- Logo -->
      <div class="login-logo-wrap">
        <img :src="logoImg" alt="My Dent Care" class="login-logo" />
        <h1 class="login-brand">My Dent Care</h1>
        <p class="login-tagline">Aplikasi Management Klinik Gigi</p>
      </div>

      <!-- Demo Banner -->
      <div class="demo-banner">
        <div class="demo-banner-title">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Coba dengan akun demo
        </div>
        <div class="demo-accounts">
          <button
            v-for="acc in demoAccounts"
            :key="acc.email"
            type="button"
            @click="fillDemo(acc)"
            class="demo-btn"
          >
            <span class="demo-btn-label">{{ acc.label }}</span>
            <span class="demo-btn-role">{{ acc.role }}</span>
          </button>
        </div>
      </div>

      <!-- Divider -->
      <div class="login-divider">
        <span>atau masuk manual</span>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Email</label>
          <input v-model="email" type="email" required placeholder="admin@clinic.id" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="password" type="password" required placeholder="••••••••" />
        </div>

        <div v-if="localError" class="alert alert-error">{{ localError }}</div>

        <button type="submit" :disabled="loading" class="btn-login">
          <svg v-if="loading" class="btn-spinner" viewBox="0 0 24 24" fill="none" width="18" height="18">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="31.4" stroke-dashoffset="31.4" class="spin-circle"/>
          </svg>
          {{ loading ? 'Masuk...' : 'Masuk' }}
        </button>
      </form>

      <p class="login-footer">Professional Dental Practice Management</p>
    </div>
  </div>
</template>

<style scoped>
.login-bg {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #14b8a6 100%);
  padding: 1.5rem;
}

.login-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 24px 64px rgba(0,0,0,0.15);
  width: 100%;
  max-width: 420px;
  padding: 2rem 2rem 1.5rem;
}

/* Logo */
.login-logo-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.5rem;
}
.login-logo {
  height: 72px;
  width: auto;
  object-fit: contain;
  margin-bottom: 0.5rem;
}
.login-brand {
  font-size: 1.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}
.login-tagline {
  font-size: 0.75rem;
  color: #9ca3af;
  margin: 2px 0 0;
}

/* Demo banner */
.demo-banner {
  background: linear-gradient(135deg, #f0f9ff, #f0fdfa);
  border: 1px solid #bae6fd;
  border-radius: 12px;
  padding: 0.875rem 1rem;
  margin-bottom: 1rem;
}
.demo-banner-title {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #0369a1;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 0.625rem;
}
.demo-accounts {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}
.demo-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: 0.5rem 0.25rem;
  background: white;
  border: 1.5px solid #bae6fd;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}
.demo-btn:hover {
  border-color: #0ea5e9;
  background: #f0f9ff;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(14,165,233,0.15);
}
.demo-btn-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #0284c7;
}
.demo-btn-role {
  font-size: 0.625rem;
  color: #9ca3af;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
  text-align: center;
}

/* Divider */
.login-divider {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0.75rem 0;
}
.login-divider::before,
.login-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}
.login-divider span {
  font-size: 0.75rem;
  color: #9ca3af;
  white-space: nowrap;
}

/* Form */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}
.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}
.form-group input {
  padding: 0.625rem 0.875rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.9375rem;
  transition: border-color 0.2s;
  outline: none;
  width: 100%;
  box-sizing: border-box;
}
.form-group input:focus {
  border-color: #0ea5e9;
  box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
}

.btn-login {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.75rem;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(14,165,233,0.3);
  margin-top: 0.25rem;
}
.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(14,165,233,0.4);
}
.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Spinner */
.btn-spinner {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Footer */
.login-footer {
  text-align: center;
  font-size: 0.6875rem;
  color: #d1d5db;
  margin-top: 1.25rem;
}
</style>
