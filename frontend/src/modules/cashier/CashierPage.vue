<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import api from '@/core/api/client'
import type { ApiResponse } from '@/shared/types/api'
import { formatRupiah, formatRupiahInput, parseRupiahInput, terbilangRupiah } from '@/shared/utils/money'

interface Patient { id: string; patient_code: string; full_name: string; phone?: string | null; email?: string | null; address?: string | null }
interface InvoiceItem { description: string; treatment_id?: string | null; qty: number; amount: number }
interface Invoice {
  id: string
  patient_id: string
  invoice_number: string
  total_amount: number
  paid_amount: number
  status: string
  due_date?: string | null
  items: InvoiceItem[]
  notes?: string | null
  patient?: Patient
  created_at?: string
}
interface Treatment {
  id: string
  patient_id: string
  doctor_id?: string | null
  treatment_type: string
  status: string
  cost: number
  description?: string | null
  patient?: Patient
  created_at?: string
}

/* ===== State ===== */
const treatments = ref<Treatment[]>([])
const invoices = ref<Invoice[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const tab = ref<'billing' | 'invoices'>('billing')
const saving = ref(false)
const msg = ref('')

const invoiceTreatment = ref<Treatment | null>(null)
const showInvoiceForm = ref(false)
const payInvoice = ref<Invoice | null>(null)
const showPayModal = ref(false)
const payAmount = ref('')
const printInvoice = ref<Invoice | null>(null)

const CLINIC = {
  name: 'My Dent Care',
  branch: 'Demo Dental Jakarta Pusat',
  address: 'Jl. Sudirman No. 123, Jakarta Pusat',
  phone: '+62 21 1234 5678',
  email: 'info@demodental.com',
}

/* ===== Computed ===== */
/** Treatments that already have an invoice are excluded from the billing tab. */
const invoicedTreatmentIds = computed(() =>
  new Set(invoices.value.flatMap((i) => i.items?.map((it) => it.treatment_id) ?? [])),
)

const billableTreatments = computed(() =>
  treatments.value.filter((t) => t.id && !invoicedTreatmentIds.value.has(t.id)),
)

const stats = computed(() => {
  const unpaid = invoices.value.filter((i) => i.status !== 'paid' && i.status !== 'void')
  const outstanding = unpaid.reduce((s, i) => s + Number(i.total_amount ?? 0) - Number(i.paid_amount ?? 0), 0)
  return {
    billable: billableTreatments.value.length,
    unpaidCount: unpaid.length,
    outstanding,
    paidToday: invoices.value
      .filter((i) => i.status === 'paid')
      .reduce((s, i) => s + Number(i.total_amount ?? 0), 0),
  }
})

/* ===== Helpers ===== */
function onMoneyInput(event: Event): void {
  const input = event.target as HTMLInputElement
  payAmount.value = input.value
  // Re-group digits without a caret jump when the grouping changes.
  const grouped = formatRupiahInput(input.value)
  if (grouped !== input.value) input.value = grouped
}

function fmtDate(d?: string | null): string {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}
function fmtDateTime(d?: string | null): string {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function statusBadge(s: string): string {
  return {
    paid: 'badge-success', unpaid: 'badge-warning', partially_paid: 'badge-info',
    overdue: 'badge-danger', draft: 'badge-gray', void: 'badge-gray',
    planned: 'badge-gray', in_progress: 'badge-warning', completed: 'badge-success',
  }[s] ?? 'badge-gray'
}
function statusLabel(s: string): string {
  return {
    paid: 'Lunas', unpaid: 'Belum Dibayar', partially_paid: 'Dibayar Sebagian',
    overdue: 'Jatuh Tempo', draft: 'Draf', void: 'Batal',
    planned: 'Direncanakan', in_progress: 'Berjalan', completed: 'Selesai',
  }[s] ?? s
}

/* ===== Data ===== */
async function fetchData(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const [tRes, iRes] = await Promise.all([
      api.get<ApiResponse<Treatment[]>>('/v1/treatments', { params: { per_page: 100 } }),
      api.get<ApiResponse<Invoice[]>>('/v1/invoices', { params: { per_page: 100 } }),
    ])
    const tr: any = tRes.data
    const ir: any = iRes.data
    treatments.value = Array.isArray(tr) ? tr : (tr?.data ?? [])
    invoices.value = Array.isArray(ir) ? ir : (ir?.data ?? [])
  } catch (e: any) {
    error.value = e?.message ?? 'Gagal memuat data kasir.'
  } finally {
    loading.value = false
  }
}

/* ===== Billing ===== */
function openBilling(t: Treatment): void {
  invoiceTreatment.value = t
  payAmount.value = formatRupiahInput(t.cost)
  msg.value = ''
  showInvoiceForm.value = true
}

async function confirmBilling(): Promise<void> {
  if (!invoiceTreatment.value) return
  const t = invoiceTreatment.value
  saving.value = true
  msg.value = ''
  try {
    const payload = {
      patient_id: t.patient_id,
      total_amount: Number(t.cost ?? 0),
      status: 'unpaid',
      items: [{ description: t.treatment_type, treatment_id: t.id, qty: 1, amount: Number(t.cost ?? 0) }],
      notes: t.description ?? null,
    }
    const { data: res } = await api.post<ApiResponse<Invoice>>('/v1/invoices', payload)
    showInvoiceForm.value = false
    await fetchData()
    // Print the freshly created bill straight away.
    const created = res.data
    if (created) await printDocument(created)
  } catch (e: any) {
    msg.value = e?.message ?? 'Gagal membuat tagihan.'
  } finally {
    saving.value = false
  }
}

/* ===== Payment ===== */
function openPayment(inv: Invoice): void {
  payInvoice.value = inv
  const remaining = Number(inv.total_amount ?? 0) - Number(inv.paid_amount ?? 0)
  payAmount.value = formatRupiahInput(remaining)
  msg.value = ''
  showPayModal.value = true
}

async function confirmPayment(): Promise<void> {
  if (!payInvoice.value) return
  const amount = parseRupiahInput(payAmount.value)
  if (amount === null || amount <= 0) {
    msg.value = 'Masukkan jumlah pembayaran yang valid.'
    return
  }
  saving.value = true
  msg.value = ''
  try {
    const newPaid = Number(payInvoice.value.paid_amount ?? 0) + amount
    await api.patch(`/v1/invoices/${payInvoice.value.id}`, { paid_amount: newPaid })
    showPayModal.value = false
    await fetchData()
  } catch (e: any) {
    msg.value = e?.message ?? 'Gagal mencatat pembayaran.'
  } finally {
    saving.value = false
  }
}

/* ===== Print ===== */
async function printDocument(inv: Invoice): Promise<void> {
  printInvoice.value = inv
  await nextTick()
  window.print()
}
function onAfterPrint(): void { printInvoice.value = null }

onMounted(() => {
  fetchData()
  window.addEventListener('afterprint', onAfterPrint)
})
onBeforeUnmount(() => window.removeEventListener('afterprint', onAfterPrint))
</script>

<template>
  <div class="cashier-page">
    <!-- Header -->
    <div class="cashier-head">
      <div>
        <h1 class="cashier-title">Kasir &amp; Penagihan</h1>
        <p class="cashier-sub">Buat tagihan treatment pasien, terima pembayaran, dan cetak bukti pembayaran.</p>
      </div>
      <button class="btn btn-primary" @click="fetchData">↻ Refresh</button>
    </div>

    <!-- Stats -->
    <div class="cashier-stats">
      <div class="stat-card"><div class="stat-num warn">{{ stats.billable }}</div><div class="stat-label">Perawatan Belum Ditagih</div></div>
      <div class="stat-card"><div class="stat-num danger">{{ formatRupiah(stats.outstanding) }}</div><div class="stat-label">Piutang Belum Dibayar</div></div>
      <div class="stat-card"><div class="stat-num ok">{{ formatRupiah(stats.paidToday) }}</div><div class="stat-label">Sudah Lunas</div></div>
      <div class="stat-card"><div class="stat-num info">{{ stats.unpaidCount }}</div><div class="stat-label">Invoice Belum Lunas</div></div>
    </div>

    <!-- Tabs -->
    <div class="cashier-tabs">
      <button :class="['tab', tab === 'billing' ? 'tab-active' : '']" @click="tab = 'billing'">
        Perawatan Belum Ditagih ({{ billableTreatments.length }})
      </button>
      <button :class="['tab', tab === 'invoices' ? 'tab-active' : '']" @click="tab = 'invoices'">
        Invoice &amp; Pembayaran ({{ invoices.length }})
      </button>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <!-- Billing tab -->
    <div v-if="tab === 'billing'">
      <div class="cashier-table-wrap">
        <table class="cashier-table">
          <thead>
            <tr>
              <th>Pasien</th><th>Tindakan</th><th>Status Perawatan</th><th class="ta-right">Biaya</th><th>Tanggal</th><th class="ta-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="6" class="status-msg">Memuat data...</td></tr>
            <tr v-else-if="!billableTreatments.length"><td colspan="6" class="status-msg">Tidak ada perawatan yang belum ditagih.</td></tr>
            <tr v-for="t in billableTreatments" :key="t.id">
              <td>
                <div class="cell-name">{{ t.patient?.full_name ?? '-' }}</div>
                <div class="cell-sub">{{ t.patient?.phone ?? '' }}</div>
              </td>
              <td><div class="cell-diag">{{ t.treatment_type }}</div><div class="cell-sub">{{ t.description ?? '' }}</div></td>
              <td><span class="badge" :class="statusBadge(t.status)">{{ statusLabel(t.status) }}</span></td>
              <td class="ta-right money">{{ formatRupiah(t.cost) }}</td>
              <td>{{ fmtDate(t.created_at) }}</td>
              <td class="ta-right">
                <button class="btn btn-primary btn-sm" @click="openBilling(t)">+ Buat Tagihan</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Invoices tab -->
    <div v-else>
      <div class="cashier-table-wrap">
        <table class="cashier-table">
          <thead>
            <tr>
              <th>No. Invoice</th><th>Pasien</th><th class="ta-right">Total</th><th class="ta-right">Dibayar</th><th>Status</th><th>Jatuh Tempo</th><th class="ta-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="7" class="status-msg">Memuat data...</td></tr>
            <tr v-else-if="!invoices.length"><td colspan="7" class="status-msg">Belum ada invoice.</td></tr>
            <tr v-for="inv in invoices" :key="inv.id">
              <td class="cell-name">{{ inv.invoice_number }}</td>
              <td>
                <div class="cell-name">{{ inv.patient?.full_name ?? '-' }}</div>
                <div class="cell-sub">{{ inv.patient?.phone ?? '' }}</div>
              </td>
              <td class="ta-right money">{{ formatRupiah(inv.total_amount) }}</td>
              <td class="ta-right money">{{ formatRupiah(inv.paid_amount) }}</td>
              <td><span class="badge" :class="statusBadge(inv.status)">{{ statusLabel(inv.status) }}</span></td>
              <td>{{ fmtDate(inv.due_date) }}</td>
              <td class="ta-right">
                <div class="row-actions">
                  <button class="btn btn-ghost btn-sm" @click="printDocument(inv)">Cetak</button>
                  <button v-if="inv.status !== 'paid' && inv.status !== 'void'" class="btn btn-primary btn-sm" @click="openPayment(inv)">Bayar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create-invoice confirm modal -->
    <transition name="fade">
      <div v-if="showInvoiceForm && invoiceTreatment" class="modal-overlay" @click.self="showInvoiceForm = false">
        <div class="modal">
          <div class="modal-head">
            <h3>Buat Tagihan</h3>
            <button class="modal-close" @click="showInvoiceForm = false">&times;</button>
          </div>
          <div class="modal-body">
            <div v-if="msg" class="alert alert-error">{{ msg }}</div>
            <div class="kv"><span class="k">Pasien</span><span class="v">{{ invoiceTreatment.patient?.full_name ?? '-' }}</span></div>
            <div class="kv"><span class="k">Tindakan</span><span class="v">{{ invoiceTreatment.treatment_type }}</span></div>
            <div class="mp-field">
              <label for="inv-total">Jumlah Tagihan (Rp)</label>
              <input
                id="inv-total"
                :value="formatRupiahInput(payAmount)"
                @input="onMoneyInput"
                inputmode="numeric"
                class="mp-input"
              />
            </div>
            <p class="hint">Tagihan akan dibuat dengan status "Belum Dibayar" dan dapat langsung dicetak. Invoice ini otomatis muncul di modul Billing portal admin.</p>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showInvoiceForm = false">Batal</button>
            <button class="btn btn-primary" :disabled="saving" @click="confirmBilling">{{ saving ? 'Memproses...' : 'Buat &amp; Cetak Tagihan' }}</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Payment modal -->
    <transition name="fade">
      <div v-if="showPayModal && payInvoice" class="modal-overlay" @click.self="showPayModal = false">
        <div class="modal">
          <div class="modal-head">
            <h3>Terima Pembayaran</h3>
            <button class="modal-close" @click="showPayModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div v-if="msg" class="alert alert-error">{{ msg }}</div>
            <div class="kv"><span class="k">No. Invoice</span><span class="v">{{ payInvoice.invoice_number }}</span></div>
            <div class="kv"><span class="k">Pasien</span><span class="v">{{ payInvoice.patient?.full_name ?? '-' }}</span></div>
            <div class="kv"><span class="k">Total Tagihan</span><span class="v money">{{ formatRupiah(payInvoice.total_amount) }}</span></div>
            <div class="kv"><span class="k">Sudah Dibayar</span><span class="v money">{{ formatRupiah(payInvoice.paid_amount) }}</span></div>
            <div class="mp-field">
              <label for="pay-amount">Jumlah Pembayaran (Rp)</label>
              <input
                id="pay-amount"
                :value="formatRupiahInput(payAmount)"
                @input="onMoneyInput"
                inputmode="numeric"
                class="mp-input"
              />
            </div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showPayModal = false">Batal</button>
            <button class="btn btn-primary" :disabled="saving" @click="confirmPayment">{{ saving ? 'Menyimpan...' : 'Catat Pembayaran' }}</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Printable invoice -->
    <div v-if="printInvoice" class="print-document">
      <div class="pd-head">
        <div>
          <h2>{{ CLINIC.name }}</h2>
          <p>{{ CLINIC.branch }}</p>
          <p>{{ CLINIC.address }}</p>
          <p>T. {{ CLINIC.phone }} · {{ CLINIC.email }}</p>
        </div>
        <div class="pd-invoice">
          <h3>INVOICE</h3>
          <p>No: {{ printInvoice.invoice_number }}</p>
          <p>Tanggal: {{ fmtDateTime(printInvoice.created_at) }}</p>
        </div>
      </div>
      <hr />
      <div class="pd-billto">
        <span class="k">Ditagihkan Kepada</span>
        <p class="strong">{{ printInvoice.patient?.full_name ?? '-' }}</p>
        <p>{{ printInvoice.patient?.phone ?? '-' }}</p>
        <p>{{ printInvoice.patient?.address ?? '' }}</p>
      </div>
      <table class="pd-table">
        <thead>
          <tr><th style="width:34px">#</th><th>Deskripsi</th><th class="ta-right">Qty</th><th class="ta-right">Harga</th><th class="ta-right">Subtotal</th></tr>
        </thead>
        <tbody>
          <tr v-for="(it, idx) in printInvoice.items" :key="idx">
            <td>{{ idx + 1 }}</td>
            <td>{{ it.description }}</td>
            <td class="ta-right">{{ it.qty }}</td>
            <td class="ta-right">{{ formatRupiah(it.amount) }}</td>
            <td class="ta-right">{{ formatRupiah(Number(it.amount) * Number(it.qty)) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="ta-right strong">Total</td>
            <td class="ta-right strong money">{{ formatRupiah(printInvoice.total_amount) }}</td>
          </tr>
          <tr>
            <td colspan="4" class="ta-right">Dibayar</td>
            <td class="ta-right money">{{ formatRupiah(printInvoice.paid_amount) }}</td>
          </tr>
          <tr>
            <td colspan="4" class="ta-right strong">Sisa</td>
            <td class="ta-right strong money">{{ formatRupiah(Number(printInvoice.total_amount) - Number(printInvoice.paid_amount)) }}</td>
          </tr>
        </tfoot>
      </table>
      <p class="pd-terbilang"><span class="k">Terbilang:</span> {{ terbilangRupiah(printInvoice.total_amount) }}</p>
      <p class="pd-status">Status: <strong>{{ statusLabel(printInvoice.status) }}</strong></p>
      <p class="pd-note" v-if="printInvoice.notes">Catatan: {{ printInvoice.notes }}</p>
      <div class="pd-foot">
        <div>
          <p>Diterima oleh,</p>
          <div class="pd-sign"></div>
          <p class="strong">{{ printInvoice.patient?.full_name ?? 'Pasien' }}</p>
        </div>
        <div>
          <p>Hormat kami,</p>
          <div class="pd-sign"></div>
          <p class="strong">Kasir · {{ CLINIC.name }}</p>
        </div>
      </div>
      <p class="pd-thanks">Terima kasih atas kepercayaan Anda kepada {{ CLINIC.name }}.</p>
    </div>
  </div>
</template>

<style scoped>
.cashier-page { color: #1e293b; }
.cashier-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
.cashier-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem; }
.cashier-sub { font-size: 0.875rem; color: #64748b; margin: 0; }

.cashier-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.875rem; margin-bottom: 1.5rem; }
.stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem 1.125rem; box-shadow: 0 1px 2px rgba(15,23,42,0.04); }
.stat-num { font-size: 1.35rem; font-weight: 800; color: #0f172a; }
.stat-num.warn { color: #b45309; } .stat-num.danger { color: #dc2626; } .stat-num.ok { color: #059669; } .stat-num.info { color: #2563eb; }
.stat-label { font-size: 0.75rem; color: #64748b; margin-top: 0.2rem; }

.cashier-tabs { display: flex; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 2px solid #e2e8f0; }
.tab { padding: 0.625rem 1rem; border: none; background: none; font-size: 0.875rem; font-weight: 600; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; font-family: inherit; }
.tab-active { color: #1d4ed8; border-bottom-color: #1d4ed8; }

.cashier-table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow-x: auto; }
.cashier-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.cashier-table th { background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; font-size: 0.72rem; padding: 0.7rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
.cashier-table td { padding: 0.7rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: top; }
.cashier-table tr:last-child td { border-bottom: none; }
.cashier-table tr:hover td { background: #f8fafc; }
.ta-right { text-align: right; }
.money { font-variant-numeric: tabular-nums; font-weight: 600; white-space: nowrap; }
.cell-name { font-weight: 600; }
.cell-sub { font-size: 0.75rem; color: #94a3b8; }
.cell-diag { max-width: 260px; }
.status-msg { text-align: center; color: #94a3b8; padding: 2rem; }
.row-actions { display: flex; gap: 0.375rem; justify-content: flex-end; }

.badge { display: inline-block; padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-gray { background: #f1f5f9; color: #475569; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-info { background: #dbeafe; color: #1e40af; }
.badge-danger { background: #fee2e2; color: #991b1b; }

.alert { padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.875rem; margin-bottom: 1rem; }
.alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); display: flex; align-items: center; justify-content: center; z-index: 200; }
.modal { background: #fff; border-radius: 16px; width: 95%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 30px 80px rgba(2,6,23,0.25); }
.modal-head { display: flex; justify-content: space-between; align-items: center; padding: 1.125rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
.modal-head h3 { margin: 0; font-size: 1.0625rem; font-weight: 800; color: #0f172a; }
.modal-close { background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; }
.modal-body { padding: 1.5rem; }
.modal-foot { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; gap: 0.625rem; justify-content: flex-end; }
.kv { display: flex; justify-content: space-between; gap: 1rem; padding: 0.375rem 0; border-bottom: 1px dashed #e5e7eb; font-size: 0.875rem; }
.kv .k { color: #64748b; }
.kv .v { font-weight: 600; text-align: right; }
.mp-field { margin: 1rem 0; }
.mp-field label { display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
.mp-input { width: 100%; padding: 0.55rem 0.7rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.9375rem; font-family: inherit; outline: none; text-align: right; }
.mp-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
.hint { font-size: 0.78rem; color: #64748b; margin-top: 0.75rem; }

.btn { display: inline-flex; align-items: center; gap: 0.4375rem; padding: 0.5rem 1rem; border: none; border-radius: 9px; font-size: 0.8125rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .15s; }
.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-ghost { background: transparent; color: #475569; border: 1.5px solid #e2e8f0; }
.btn-ghost:hover { border-color: #2563eb; color: #2563eb; }
.btn-sm { padding: 0.35rem 0.6rem; font-size: 0.75rem; }

.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ===== Print ===== */
.print-document { display: none; }
</style>

<style>
/* Unscoped so `body *` reaches the portal chrome. Print styles are global on
   purpose: only the invoice sheet may be visible on paper. */
@media print {
  body * { visibility: hidden; }
  .print-document, .print-document * { visibility: visible; }
  .print-document {
    display: block !important;
    position: absolute; inset: 0; left: 0; top: 0;
    width: 100%; padding: 0; margin: 0;
    background: #fff; color: #111827; font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
  }
}

.print-document { color: #111827; font-family: Arial, Helvetica, sans-serif; }
.pd-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; }
.pd-head h2 { margin: 0 0 0.15rem; font-size: 20px; letter-spacing: 0.5px; }
.pd-head p { margin: 0; font-size: 11px; color: #4b5563; }
.pd-invoice { text-align: right; }
.pd-invoice h3 { margin: 0 0 0.2rem; font-size: 16px; letter-spacing: 2px; }
.pd-invoice p { margin: 0; font-size: 11px; }
.print-document hr { border: none; border-top: 2px solid #111827; margin: 0.75rem 0; }
.pd-billto { margin: 0.5rem 0 1rem; }
.pd-billto .k { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; }
.pd-billto p { margin: 0.1rem 0; font-size: 12px; }
.strong { font-weight: 700; }
.pd-table { width: 100%; border-collapse: collapse; margin: 0.5rem 0; }
.pd-table th { border: 1px solid #9ca3af; padding: 6px 8px; background: #f3f4f6; font-size: 11px; text-align: left; }
.pd-table td { border: 1px solid #d1d5db; padding: 6px 8px; font-size: 12px; }
.pd-table tfoot td { background: #f9fafb; }
.pd-terbilang { font-size: 11px; margin: 0.75rem 0 0.25rem; font-style: italic; }
.pd-terbilang .k { font-style: normal; color: #6b7280; }
.pd-status { font-size: 12px; margin: 0.25rem 0; }
.pd-note { font-size: 11px; color: #4b5563; margin: 0.25rem 0; }
.pd-foot { display: flex; justify-content: space-between; gap: 3rem; margin-top: 2.5rem; }
.pd-foot p { margin: 0; font-size: 11px; }
.pd-sign { border-bottom: 1.5px solid #4b5563; width: 200px; height: 2.5rem; margin-top: 0.25rem; }
.pd-thanks { text-align: center; font-size: 11px; color: #6b7280; margin-top: 2rem; }
</style>
