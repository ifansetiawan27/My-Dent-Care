/**
 * Mock backend for local frontend preview only.
 *
 * Pure Node.js (no dependencies). Emulates the Laravel API contract the
 * frontend consumes: the auth flow and Laravel-style paginated lists.
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
  'crm.view',
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

const DOCTOR_PROFILE = {
  id: DOCTOR_ID,
  organization_id: ORG_ID,
  branch_id: BRANCH_ID,
  doctor_code: 'DOC20260001',
  full_name: 'drg. Jane Doe',
  phone: '+62 813 8888 9999',
  email: 'jane.doe@demodental.com',
  gender: 'female',
  is_active: true,
}

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
  patient: { id: PATIENTS[patientIdx].id, full_name: PATIENTS[patientIdx].full_name, phone: PATIENTS[patientIdx].phone },
  doctor: { id: DOCTOR_ID, full_name: DOCTOR_PROFILE.full_name },
})

const APPOINTMENTS = [
  appointment('1', 0, 0, new Date().getHours() + 1, 0, 'confirmed', 'checkup', 'Kontrol rutin.'),
  appointment('2', 1, 0, new Date().getHours() + 2, 30, 'confirmed', 'cleaning', 'Scaling & polishing.'),
  appointment('3', 2, 0, 16, 0, 'scheduled', 'consultation', 'Konsultasi awal.'),
  appointment('4', 0, 1, 10, 0, 'scheduled', 'treatment', 'Perawatan saluran akar.'),
  appointment('5', 1, 2, 14, 30, 'scheduled', 'consultation', 'Konsultasi orthodonti.'),
  appointment('6', 3, 3, 9, 0, 'completed', 'checkup', 'Selesai.'),
]

// --- Helpers ---------------------------------------------------------------
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

    // --- Lists (Laravel paginator shape) --------------------------------
    if (path === '/api/v1/appointments' && method === 'GET') {
      send(res, 200, paginate(APPOINTMENTS, path, query))
      return
    }

    if (path === '/api/v1/patients' && method === 'GET') {
      const search = (query.search || '').toLowerCase()
      const filtered = search
        ? PATIENTS.filter((p) => (p.full_name + p.email + p.phone + p.patient_code).toLowerCase().includes(search))
        : PATIENTS
      send(res, 200, paginate(filtered, path, query))
      return
    }

    // --- Fallback: empty paginator so module pages render empty states ---
    if (path.startsWith('/api/v1/') && method === 'GET') {
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
