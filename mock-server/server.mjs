/**
 * Mock backend for local frontend preview only.
 *
 * Pure Node.js (no dependencies). Emulates the Laravel API contract the
 * frontend consumes: the auth flow, Laravel-style paginated lists, and
 * persistent CRUD for the core resources so form input actually "sticks"
 * for the duration of the preview session.
 *
 * Demo data mirrors DentalERP/database/seeders/DemoSeeder.php and permissions
 * mirror RolePermissionSeeder.php so portal routing/nav behaves realistically.
 *
 * NEVER deploy this. It has no real validation, no database, and accepts any
 * of the demo passwords.
 */

import http from 'node:http'

const HOST = '127.0.0.1'
const PORT = 8080

// --- Fixed demo identifiers (stable across restarts) -----------------------
const ORG_ID = '0192f0a0-0000-7000-8000-000000000001'
const BRANCH_ID = '0192f0a0-0000-7000-8000-000000000002'
const DOCTOR_ID = '0192f0a0-0000-7000-8000-000000000003'

const ORG_NAME = 'Demo Dental Clinic Group'
const BRANCH_NAME = 'Demo Dental Jakarta Pusat'

const CLINIC = {
  name: 'My Dent Care',
  org: ORG_NAME,
  branch: BRANCH_NAME,
  address: 'Jl. Sudirman No. 123, Jakarta Pusat',
  phone: '+62 21 1234 5678',
  email: 'info@demodental.com',
}

/** Every permission defined by RolePermissionSeeder (super_admin gets all). */
const ALL_PERMISSIONS = [
  'organization.view', 'organization.update',
  'branch.view', 'branch.create', 'branch.update', 'branch.delete', 'branch.restore',
  'user.view', 'user.create', 'user.update', 'user.delete', 'user.restore',
  'role.view', 'role.assign',
  'permission.view', 'permission.assign',
  'patient.view', 'patient.create', 'patient.update', 'patient.delete', 'patient.export',
  'appointment.view', 'appointment.create', 'appointment.update', 'appointment.delete', 'appointment.export',
  'medical_record.view', 'medical_record.create', 'medical_record.update', 'medical_record.delete',
  'odontogram.view', 'odontogram.create', 'odontogram.update',
  'treatment.view', 'treatment.create', 'treatment.update', 'treatment.delete',
  'billing.view', 'billing.create', 'billing.update', 'billing.delete', 'billing.export',
  'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.export',
  'finance.view', 'finance.create', 'finance.update', 'finance.delete', 'finance.export',
  'asset.view', 'asset.create', 'asset.update', 'asset.delete',
  'crm.view', 'crm.create', 'crm.update', 'crm.delete',
  'dashboard.view', 'dashboard.export',
  'report.view', 'report.export',
]

const DOCTOR_PERMISSIONS = [
  'patient.view', 'patient.create', 'patient.update',
  'appointment.view', 'appointment.create', 'appointment.update',
  'medical_record.view', 'medical_record.create', 'medical_record.update',
  'odontogram.view', 'odontogram.create', 'odontogram.update',
  'treatment.view', 'treatment.create', 'treatment.update',
  'dashboard.view',
]

const RECEPTIONIST_PERMISSIONS = [
  'patient.view', 'patient.create', 'patient.update',
  'appointment.view', 'appointment.create', 'appointment.update', 'appointment.delete',
  'treatment.view',
  'crm.view',
  'billing.view', 'billing.create', 'billing.update',
  'finance.view',
  'dashboard.view',
]

/** Shape matches ProfileResource / UserSummaryResource. */
const USERS = [
  {
    id: '0192f0a0-0000-7000-8000-0000000000a1',
    employee_code: 'SA001',
    name: 'Super Admin Demo',
    username: 'superadmin',
    email: 'superadmin@demodental.com',
    phone: '+62 812 3456 7890',
    password: 'password123',
    gender: 'male',
    status: 'active',
    roles: ['super_admin'],
    permissions: ALL_PERMISSIONS,
  },
  {
    id: '0192f0a0-0000-7000-8000-0000000000a2',
    employee_code: 'DOC001',
    name: 'Dr. Jane Smith',
    username: 'drjane',
    email: 'drjane@demodental.com',
    phone: '+62 813 9876 5432',
    password: 'password123',
    gender: 'female',
    status: 'active',
    roles: ['doctor'],
    permissions: DOCTOR_PERMISSIONS,
  },
  {
    id: '0192f0a0-0000-7000-8000-0000000000a3',
    employee_code: 'REC001',
    name: 'Sarah Receptionist',
    username: 'sarah',
    email: 'sarah@demodental.com',
    phone: '+62 814 5555 6666',
    password: 'password123',
    gender: 'female',
    status: 'active',
    roles: ['receptionist'],
    permissions: RECEPTIONIST_PERMISSIONS,
  },
]

const DOCTORS = [
  {
    id: DOCTOR_ID,
    organization_id: ORG_ID,
    branch_id: BRANCH_ID,
    doctor_code: 'DOC20260001',
    full_name: 'drg. Jane Doe',
    license_number: 'STR/12345/JKT',
    specialty: 'Odontologi Konservasi',
    consultation_fee: 250000,
    phone: '+62 813 8888 9999',
    email: 'jane.doe@demodental.com',
    gender: 'female',
    is_active: true,
  },
  {
    id: '0192f0a0-0000-7000-8000-000000000004',
    organization_id: ORG_ID,
    branch_id: BRANCH_ID,
    doctor_code: 'DOC20260002',
    full_name: 'drg. Budi Pratama',
    license_number: 'STR/67890/JKT',
    specialty: 'Ortodonsia',
    consultation_fee: 300000,
    phone: '+62 856 7777 8888',
    email: 'budi.pratama@demodental.com',
    gender: 'male',
    is_active: true,
  },
]

// --- Demo data (relative to "now" so dashboards look alive) ----------------
const pad = (n) => String(n).padStart(2, '0')
const localISO = (d) =>
  `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}:00.000Z`

const at = (dayOffset, hour, minute) => {
  const d = new Date()
  d.setDate(d.getDate() + dayOffset)
  d.setHours(hour, minute, 0, 0)
  return localISO(d)
}

const patient = (code, name, phone, email, gender, birth) => ({
  id: `0192f0a0-0000-7000-8000-0000000000${code}`,
  patient_code: `PAT2026000${code}`,
  full_name: name,
  birth_date: birth,
  gender,
  blood_type: null,
  religion: null,
  marital_status: null,
  nationality_id: null,
  patient_type_id: null,
  patient_type: null,
  organization_id: ORG_ID,
  branch_id: BRANCH_ID,
  phone,
  email,
  address: 'Jakarta, Indonesia',
  district_id: null,
  village_id: null,
  is_active: true,
  created_at: at(-30, 9, 0),
  updated_at: at(-1, 9, 0),
})

const PATIENTS = [
  patient('1', 'John Doe', '+62 815 1111 2222', 'john.doe@example.com', 'male', '1990-05-15'),
  patient('2', 'Maria Garcia', '+62 816 3333 4444', 'maria.garcia@example.com', 'female', '1985-08-20'),
  patient('3', 'Robert Chen', '+62 817 5555 6666', 'robert.chen@example.com', 'male', '1995-03-10'),
  patient('4', 'Siti Rahayu', '+62 818 7777 8888', 'siti.rahayu@example.com', 'female', '1988-11-02'),
  patient('5', 'Budi Santoso', '+62 819 9999 0000', 'budi.santoso@example.com', 'male', '1975-01-22'),
]

const patientSummary = (id) => {
  const p = PATIENTS.find((x) => x.id === id)
  return p ? { id: p.id, patient_code: p.patient_code, full_name: p.full_name, phone: p.phone, email: p.email } : null
}
const doctorSummary = (id) => {
  const d = DOCTORS.find((x) => x.id === id)
  return d ? { id: d.id, doctor_code: d.doctor_code, full_name: d.full_name, specialty: d.specialty } : null
}

const appointment = (code, patientIdx, dayOffset, hour, minute, status, type, notes) => ({
  id: `0192f0a0-0000-7000-8000-0000000000b${code}`,
  organization_id: ORG_ID,
  branch_id: BRANCH_ID,
  patient_id: PATIENTS[patientIdx].id,
  doctor_id: DOCTOR_ID,
  scheduled_at: at(dayOffset, hour, minute),
  end_at: at(dayOffset, hour, minute + 30),
  status,
  type,
  notes,
  reminder_minutes: 30,
  reminder_sent: false,
  patient: patientSummary(PATIENTS[patientIdx].id),
  doctor: doctorSummary(DOCTOR_ID),
})

const APPOINTMENTS = [
  appointment('1', 0, 0, new Date().getHours() + 1, 0, 'confirmed', 'checkup', 'Kontrol rutin.'),
  appointment('2', 1, 0, new Date().getHours() + 2, 30, 'confirmed', 'cleaning', 'Scaling & polishing.'),
  appointment('3', 2, 0, 16, 0, 'scheduled', 'consultation', 'Konsultasi awal.'),
  appointment('4', 0, 1, 10, 0, 'scheduled', 'treatment', 'Perawatan saluran akar.'),
  appointment('5', 1, 2, 14, 30, 'scheduled', 'consultation', 'Konsultasi orthodonti.'),
  appointment('6', 3, 3, 9, 0, 'completed', 'checkup', 'Selesai.'),
]

const treatment = (code, patientId, treatmentType, cost, status, description, dayOffset, tooth) => ({
  id: `0192f0a0-0000-7000-8000-0000000000c${code}`,
  organization_id: ORG_ID,
  branch_id: BRANCH_ID,
  patient_id: patientId,
  doctor_id: DOCTOR_ID,
  appointment_id: null,
  treatment_type: treatmentType,
  status,
  cost,
  description,
  procedure_data: tooth ? { tooth_number: tooth } : null,
  patient: patientSummary(patientId),
  doctor: doctorSummary(DOCTOR_ID),
  created_at: at(dayOffset, 9, 0),
  updated_at: at(dayOffset, 9, 0),
})

const TREATMENTS = [
  treatment('1', PATIENTS[0].id, 'Scaling & Polishing', 350000, 'completed', 'Pembersihan karang gigi pada gigi atas dan bawah.', -7, null),
  treatment('2', PATIENTS[0].id, 'Perawatan Saluran Akar', 1250000, 'in_progress', 'Root canal gigi 36 (molar bawah kiri).', -3, '36'),
  treatment('3', PATIENTS[1].id, 'Tambalan Gigi (Composite)', 450000, 'completed', 'Restorasi composite gigi 21.', -5, '21'),
  treatment('4', PATIENTS[2].id, 'Konsultasi & Pemeriksaan', 150000, 'completed', 'Konsultasi keluhan nyeri gigi.', -2, null),
  treatment('5', PATIENTS[3].id, 'Pencabutan Gigi', 500000, 'planned', 'Ekstraksi gigi bungsu (48).', 0, '48'),
  treatment('6', PATIENTS[4].id, 'Kawat Gigi / Orthodontic', 4500000, 'planned', 'Pemasangan kawat gigi tahap 1.', 0, null),
]

const emr = (code, patientId, dayOffset, data) => ({
  id: `0192f0a0-0000-7000-8000-0000000000d${code}`,
  organization_id: ORG_ID,
  branch_id: BRANCH_ID,
  patient_id: patientId,
  doctor_id: DOCTOR_ID,
  appointment_id: null,
  examination_date: at(dayOffset, 10, 0),
  tooth_number: data.tooth_number ?? null,
  icd_code: data.icd_code ?? null,
  chief_complaint: data.chief_complaint,
  present_illness: data.present_illness ?? null,
  medical_history: data.medical_history ?? null,
  allergies: data.allergies ?? null,
  vital_signs: data.vital_signs ?? null,
  extra_oral_exam: data.extra_oral_exam ?? null,
  intra_oral_exam: data.intra_oral_exam ?? null,
  radiology_findings: data.radiology_findings ?? null,
  diagnosis: data.diagnosis,
  secondary_diagnosis: data.secondary_diagnosis ?? null,
  treatment_notes: data.treatment_notes ?? null,
  treatment_plan: data.treatment_plan ?? null,
  prescription: data.prescription ?? null,
  follow_up_plan: data.follow_up_plan ?? null,
  status: data.status ?? 'completed',
  patient: patientSummary(patientId),
  doctor: doctorSummary(DOCTOR_ID),
  created_at: at(dayOffset, 10, 0),
  updated_at: at(dayOffset, 10, 0),
})

const EMRS = [
  emr('1', PATIENTS[0].id, -7, {
    tooth_number: '36',
    icd_code: 'K04.5',
    chief_complaint: 'Nyeri berdenyut pada gigi geraham bawah kiri sejak 3 hari yang lalu, terutama saat mengunyah dan saat meminum air dingin.',
    present_illness: 'Nyeri dirasakan terus-menerus, meningkat pada malam hari, mengganggu tidur. Pasien sudah minum obat pereda nyeri namun hanya mereda sementara.',
    medical_history: 'Tidak ada riwayat penyakit sistemik. Tidak ada alergi obat yang diketahui.',
    allergies: 'Tidak ada alergi yang diketahui.',
    vital_signs: { blood_pressure: '120/80', pulse: 76, temperature: 36.8, respiratory_rate: 18, weight: 70, height: 170, spo2: 98 },
    extra_oral_exam: 'Asimetri wajah tidak ditemukan. Tidak ada pembengkakan pada regio bukal dan submandibula. Kelenjar getah bening tidak teraba membesar.',
    intra_oral_exam: 'Gigi 36 terdapat tumpatan besar dengan bagian tepi retak. Sonde terasa sakit pada permukaan oklusal. Perkusi gigi 36 positif (+).',
    radiology_findings: 'Foto periapikal gigi 36 menunjukkan radiolusen pada daerah furkasi dan periapikal mesial akar, mencurigakan invasi ruang pulpa.',
    diagnosis: 'Pulpitis ireversibel gigi 36',
    secondary_diagnosis: 'Periodontitis apikalis akut gigi 36',
    treatment_notes: 'Dilakukan perawatan saluran akar gigi 36 tahap 1: pembukaan kamar pulpa, ekstirpasi jaringan pulpa nekrosis, irigasi NaOCl 2,5%, dan penempatan kalsium hidroksida.',
    treatment_plan: 'Lanjut perawatan saluran akar tahap 2 (2 minggu), lalu tahap 3 obturasi. Direncanakan restorasi mahkota gigi 36 setelah selesai.',
    prescription: 'Amoxicillin 500 mg 3x1 (7 hari), Metronidazole 500 mg 3x1 (5 hari), Ibuprofen 400 mg 3x1 bila nyeri.',
    follow_up_plan: 'Kontrol 2 minggu lagi untuk lanjutan perawatan saluran akar. Hubungi klinik bila nyeri memburuk atau bengkak.',
  }),
  emr('2', PATIENTS[1].id, -5, {
    tooth_number: '21',
    icd_code: 'K02.1',
    chief_complaint: 'Gigi depan atas terasa kasar dan ada lubang kecil, warna gigi menguning sejak 1 bulan lalu.',
    present_illness: 'Tidak ada nyeri spontan. Kadang terasa ngilu saat menyikat gigi atau meminum minuman dingin.',
    medical_history: 'Sehat secara umum. Tidak dalam pengobatan tertentu.',
    allergies: 'Tidak ada.',
    vital_signs: { blood_pressure: '110/70', pulse: 72, temperature: 36.6, respiratory_rate: 16, weight: 58, height: 160, spo2: 99 },
    extra_oral_exam: 'Tidak ada kelainan. Wajah simetris.',
    intra_oral_exam: 'Gigi 21 terdapat kavitas kecil pada permukaan palatal dengan warna coklat kehitaman. Sonde negatif, perkusi negatif.',
    radiology_findings: 'Radiolusen terbatas pada enamel dan dentin, tidak mencapai ruang pulpa.',
    diagnosis: 'Karies dentin gigi 21 tanpa komplikasi pulpa',
    secondary_diagnosis: null,
    treatment_notes: 'Dilakukan tumpatan composite gigi 21 setelah tindakan ekskavasi jaringan karies dan aplikasi basis kalsium hidroksida.',
    treatment_plan: 'Kontrol 6 bulan untuk evaluasi tumpatan. Jaga kebersihan oral dan hindari makanan keras/manis berlebih.',
    prescription: 'Obat kumur Chlorhexidine 0,2% 2x1 (7 hari).',
    follow_up_plan: 'Kontrol 6 bulan untuk pemeriksaan rutin.',
  }),
]

const invoice = (code, seq, patientId, items, status, dayOffset, notes) => {
  const total = items.reduce((s, it) => s + Number(it.amount ?? it.cost ?? 0), 0)
  return {
    id: `0192f0a0-0000-7000-8000-0000000000e${code}`,
    organization_id: ORG_ID,
    branch_id: BRANCH_ID,
    patient_id: patientId,
    invoice_number: `INV-${new Date().getFullYear()}${pad(new Date().getMonth() + 1)}${pad(new Date().getDate())}-${pad(seq)}`,
    total_amount: total,
    paid_amount: status === 'paid' ? total : 0,
    status,
    due_date: at(dayOffset + 14, 23, 59),
    items: items.map((it) => ({
      description: it.description ?? it.treatment_type,
      treatment_id: it.treatment_id ?? null,
      qty: 1,
      amount: Number(it.amount ?? it.cost ?? 0),
    })),
    notes: notes ?? null,
    patient: patientSummary(patientId),
    created_at: at(dayOffset, 11, 0),
    updated_at: at(dayOffset, 11, 0),
  }
}

const INVOICES = [
  invoice('1', 1, PATIENTS[0].id, [{ description: 'Scaling & Polishing', treatment_id: TREATMENTS[0].id, cost: 350000 }], 'paid', -7),
  invoice('2', 2, PATIENTS[1].id, [{ description: 'Tambalan Gigi (Composite)', treatment_id: TREATMENTS[2].id, cost: 450000 }], 'unpaid', -5),
  invoice('3', 3, PATIENTS[2].id, [{ description: 'Konsultasi & Pemeriksaan', treatment_id: TREATMENTS[3].id, cost: 150000 }], 'unpaid', -2),
]

// --- Mutable resource registry ----------------------------------------------
/** Records created at runtime get real timestamps and stable-looking ids. */
function newId(prefix, n) {
  const hex = (n + 0x100000).toString(16).slice(2)
  return `0192f0a0-0000-7000-8000-${prefix}${hex.padStart(6, '0').slice(0, 6)}`
}

const STORES = {
  patients: { rows: PATIENTS, seq: 0 },
  appointments: { rows: APPOINTMENTS, seq: 0 },
  emrs: { rows: EMRS, seq: 0 },
  treatments: { rows: TREATMENTS, seq: 0 },
  invoices: { rows: INVOICES, seq: 0 },
}

/** Endpoints that list a fixed/derived collection (no persistence needed). */
const STATIC_LISTS = {
  doctors: () => DOCTORS,
}

/** Attach the summary relations a list/table expects after create/update. */
function attachRelations(resource, row) {
  if (resource === 'patients') return row
  const next = { ...row }
  if ('patient_id' in next) next.patient = patientSummary(next.patient_id)
  if ('doctor_id' in next) next.doctor = doctorSummary(next.doctor_id)
  return next
}

function nextInvoiceNumber() {
  const d = new Date()
  const ymd = `${d.getFullYear()}${pad(d.getMonth() + 1)}${pad(d.getDate())}`
  const seq = String(INVOICES.length + 1).padStart(5, '0')
  return `INV-${ymd}-${seq}`
}

/** Laravel paginator shape: { data, links, meta } at the response root. */
function paginate(items, url, query) {
  const page = Math.max(1, parseInt(query.page, 10) || 1)
  const perPage = Math.min(100, Math.max(1, parseInt(query.per_page, 10) || 20))
  const total = items.length
  const lastPage = Math.max(1, Math.ceil(total / perPage))
  const start = (page - 1) * perPage
  const slice = items.slice(start, start + perPage)
  const link = (p) => (p === null ? null : `${url}?page=${p}&per_page=${perPage}`)
  return {
    data: slice,
    links: { first: link(1), last: link(lastPage), prev: page > 1 ? link(page - 1) : null, next: page < lastPage ? link(page + 1) : null },
    meta: {
      current_page: page,
      from: slice.length ? start + 1 : null,
      to: slice.length ? start + slice.length : null,
      per_page: perPage,
      total,
      last_page: lastPage,
    },
  }
}

const send = (res, status, body) => {
  const json = JSON.stringify(body)
  res.writeHead(status, {
    'Content-Type': 'application/json',
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Credentials': 'true',
    'Access-Control-Allow-Headers': 'Authorization, Content-Type, X-Requested-With, Accept',
    'Access-Control-Allow-Methods': 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
  })
  res.end(json)
}

const ok = (res, data, message = 'OK') => send(res, 200, { success: true, message, data })

function readBody(req) {
  return new Promise((resolve) => {
    let raw = ''
    req.on('data', (chunk) => { raw += chunk })
    req.on('end', () => {
      try { resolve(raw ? JSON.parse(raw) : {}) } catch { resolve({}) }
    })
  })
}

// --- Server ----------------------------------------------------------------
const server = http.createServer(async (req, res) => {
  const urlObj = new URL(req.url, `http://${HOST}:${PORT}`)
  const path = urlObj.pathname.replace(/\/+$/, '')
  const query = Object.fromEntries(urlObj.searchParams.entries())
  const method = (req.method || 'GET').toUpperCase()

  if (method === 'OPTIONS') { send(res, 204, null); return }

  // Match "/api/v1/<resource>" or "/api/v1/<resource>/<id>[/<action>]".
  const segments = path.split('/').filter(Boolean) // ["api","v1","invoices","<id>"]
  const resource = segments[2]
  const itemId = segments[3]
  const action = segments[4]

  try {
    // --- Auth -----------------------------------------------------------
    if (path === '/api/v1/auth/lookup' && method === 'POST') {
      const body = await readBody(req)
      const user = findUserByIdentifier(body.identifier)
      if (!user) {
        send(res, 404, { success: false, message: 'User not found.', data: null })
        return
      }
      ok(res, {
        organization_id: ORG_ID,
        branch_id: BRANCH_ID,
        organization: ORG_NAME,
        branch: BRANCH_NAME,
      }, 'Organization resolved.')
      return
    }

    if (path === '/api/v1/auth/login' && method === 'POST') {
      const body = await readBody(req)
      const user = findUserByIdentifier(body.identifier)
      if (!user || body.password !== user.password) {
        send(res, 422, {
          success: false,
          message: 'Invalid credentials.',
          data: null,
          errors: { identifier: ['Email/username atau password salah.'] },
        })
        return
      }
      ok(res, {
        token_type: 'Bearer',
        access_token: `mock-token.${user.id}.${Date.now()}`,
        access_token_expires_at: new Date(Date.now() + 60 * 60 * 1000).toISOString(),
        refresh_token: `mock-refresh.${user.id}.${Date.now()}`,
        refresh_token_expires_at: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
        device_id: 'mock-device',
        user: publicUser(user),
        roles: user.roles,
        permissions: user.permissions,
      }, 'Login successful.')
      return
    }

    if (path === '/api/v1/auth/profile' && method === 'GET') {
      const auth = (req.headers.authorization || '').replace(/^Bearer\s+/i, '')
      const match = /^mock-token\.([0-9a-f-]+)\./.exec(auth)
      const user = USERS.find((u) => u.id === match?.[1]) || USERS[0]
      ok(res, publicUser(user), 'Profile retrieved.')
      return
    }

    if (path === '/api/v1/auth/logout' && method === 'POST') {
      ok(res, null, 'Logged out.')
      return
    }

    // --- Static lists ---------------------------------------------------
    if (method === 'GET' && !itemId && STATIC_LISTS[resource]) {
      send(res, 200, paginate(STATIC_LISTS[resource](), path, query))
      return
    }

    // --- Cashier: invoice a treatment ----------------------------------
    // POST /api/v1/invoices  (also the plain create path used by module pages)
    if (resource === 'invoices' && method === 'POST' && !itemId) {
      const body = await readBody(req)
      const total = Number(body.total_amount ?? body.total ?? 0) || 0
      const row = {
        id: newId('e', ++STORES.invoices.seq),
        organization_id: body.organization_id || ORG_ID,
        branch_id: body.branch_id || BRANCH_ID,
        patient_id: body.patient_id || null,
        invoice_number: body.invoice_number || nextInvoiceNumber(),
        total_amount: total,
        paid_amount: Number(body.paid_amount ?? 0) || 0,
        status: total > 0 ? (body.status || 'unpaid') : (body.status || 'draft'),
        due_date: body.due_date || null,
        items: Array.isArray(body.items) ? body.items.map((it) => ({
          description: it.description ?? it.treatment_type ?? 'Tindakan',
          treatment_id: it.treatment_id ?? it.id ?? null,
          qty: it.qty ?? 1,
          amount: Number(it.amount ?? it.cost ?? 0) || 0,
        })) : [],
        notes: body.notes ?? null,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      }
      const withRels = attachRelations('invoices', row)
      STORES.invoices.rows.unshift(withRels)
      ok(res, withRels, 'Invoice created.')
      return
    }

    // --- Cashier: record a payment / update an invoice ------------------
    if (resource === 'invoices' && itemId && (method === 'PUT' || method === 'PATCH')) {
      const body = await readBody(req)
      const idx = STORES.invoices.rows.findIndex((r) => r.id === itemId)
      if (idx === -1) { send(res, 404, { success: false, message: 'Invoice not found.', data: null }); return }
      const cur = STORES.invoices.rows[idx]
      const paid = body.paid_amount !== undefined ? Number(body.paid_amount) : cur.paid_amount
      let status = body.status || cur.status
      if (paid >= cur.total_amount && cur.total_amount > 0) status = 'paid'
      else if (paid > 0) status = 'partially_paid'
      const updated = attachRelations('invoices', {
        ...cur,
        paid_amount: paid,
        status,
        notes: body.notes ?? cur.notes,
        due_date: body.due_date ?? cur.due_date,
        updated_at: new Date().toISOString(),
      })
      STORES.invoices.rows[idx] = updated
      ok(res, updated, 'Invoice updated.')
      return
    }

    // --- Generic persisted CRUD ----------------------------------------
    if (STORES[resource] && !action) {
      const store = STORES[resource]

      // GET list (with light search + paginator shape).
      if (method === 'GET' && !itemId) {
        const search = (query.search || '').toLowerCase()
        let rows = store.rows
        if (search) {
          rows = rows.filter((r) =>
            JSON.stringify(r).toLowerCase().includes(search))
        }
        send(res, 200, paginate(rows, path, query))
        return
      }

      // GET one.
      if (method === 'GET' && itemId) {
        const row = store.rows.find((r) => r.id === itemId)
        if (!row) { send(res, 404, { success: false, message: 'Not found.', data: null }); return }
        ok(res, row, 'OK')
        return
      }

      // CREATE.
      if (method === 'POST' && !itemId) {
        const body = await readBody(req)
        const row = attachRelations(resource, {
          ...body,
          id: newId(resource === 'patients' ? 'f' : resource === 'appointments' ? 'b' : resource === 'emrs' ? 'd' : resource === 'treatments' ? 'c' : 'e', ++store.seq),
          organization_id: body.organization_id || ORG_ID,
          branch_id: body.branch_id || BRANCH_ID,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        })
        store.rows.unshift(row)
        ok(res, row, 'Created.')
        return
      }

      // UPDATE.
      if ((method === 'PUT' || method === 'PATCH') && itemId) {
        const body = await readBody(req)
        const idx = store.rows.findIndex((r) => r.id === itemId)
        if (idx === -1) { send(res, 404, { success: false, message: 'Not found.', data: null }); return }
        const updated = attachRelations(resource, {
          ...store.rows[idx],
          ...body,
          id: itemId,
          updated_at: new Date().toISOString(),
        })
        store.rows[idx] = updated
        ok(res, updated, 'Updated.')
        return
      }

      // DELETE.
      if (method === 'DELETE' && itemId) {
        const idx = store.rows.findIndex((r) => r.id === itemId)
        if (idx !== -1) store.rows.splice(idx, 1)
        ok(res, { id: itemId }, 'Deleted.')
        return
      }
    }

    // --- Fallback: empty paginator so module pages render empty states ---
    if (method === 'GET' && path.startsWith('/api/v1/')) {
      send(res, 200, paginate([], path, query))
      return
    }

    // --- Write fallback (module forms etc.): accept and echo -------------
    if (path.startsWith('/api/v1/') && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
      const body = await readBody(req)
      ok(res, { ...body, id: 'mock-record' }, 'Saved (mock).')
      return
    }

    send(res, 404, { success: false, message: 'Not found.', data: null })
  } catch (err) {
    send(res, 500, { success: false, message: `Mock error: ${err.message}`, data: null })
  }
})

const publicUser = (u) => ({
  id: u.id,
  employee_code: u.employee_code,
  name: u.name,
  username: u.username,
  email: u.email,
  phone: u.phone,
  photo: null,
  gender: u.gender,
  gender_label: u.gender === 'male' ? 'Male' : 'Female',
  birth_date: null,
  organization_id: ORG_ID,
  branch_id: BRANCH_ID,
  status: u.status,
  status_label: 'Active',
  organization: {
    id: ORG_ID,
    company_code: 'DEMO001',
    company_name: ORG_NAME,
    legal_name: 'PT Demo Dental Indonesia',
    email: 'info@demodental.com',
    phone: '+62 21 1234 5678',
    timezone: 'Asia/Jakarta',
    currency: 'IDR',
    status: 'active',
  },
  branch: {
    id: BRANCH_ID,
    branch_code: 'JKT001',
    branch_name: BRANCH_NAME,
    timezone: 'Asia/Jakarta',
    status: 'active',
  },
  roles: u.roles,
  permissions: u.permissions,
})

const findUserByIdentifier = (identifier) =>
  USERS.find(
    (u) =>
      u.email.toLowerCase() === String(identifier || '').toLowerCase() ||
      u.username.toLowerCase() === String(identifier || '').toLowerCase(),
  )

server.listen(PORT, HOST, () => {
  console.log('============================================================')
  console.log(' My Dent Care — MOCK backend (LOCAL PREVIEW ONLY)')
  console.log('============================================================')
  console.log(` Listening  http://${HOST}:${PORT}`)
  console.log('')
  console.log(' Demo accounts (password: password123):')
  console.log('   super_admin -> Admin Console        superadmin@demodental.com')
  console.log('   doctor      -> Klinik Dokter        drjane@demodental.com')
  console.log('   receptionist-> Front Desk           sarah@demodental.com')
  console.log('')
  console.log(' Press Ctrl+C to stop.')
  console.log('============================================================')
})
