<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface UserRecord {
  id: string
  name: string
  email: string
  role: string
  status: string
  phone: string
  permissions: string[]
  created_at: string
}

const emptyForm = (): UserRecord => ({
  id: '', name: '', email: '', role: 'staff', status: 'active', phone: '',
  permissions: [], created_at: new Date().toISOString(),
})

const users = ref<UserRecord[]>([])
const roles = ['super_admin', 'admin', 'doctor', 'receptionist', 'staff']
const search = ref('')
const showModal = ref(false)
const form = ref<UserRecord>(emptyForm())
const formPermissions = ref<Set<string>>(new Set())
const editing = ref(false)
const saveMsg = ref('')

interface PermGroup {
  title: string
  permissions: { key: string; label: string }[]
}

const permissionGroups: PermGroup[] = [
  {
    title: 'Main',
    permissions: [
      { key: 'dashboard', label: 'Dashboard' },
      { key: 'appointments', label: 'Appointment' },
      { key: 'patients', label: 'Patients' },
      { key: 'emr', label: 'Medical Records (EMR)' },
      { key: 'odontogram', label: 'Odontogram' },
      { key: 'treatment', label: 'Treatment' },
    ],
  },
  {
    title: 'Operations',
    permissions: [
      { key: 'billing', label: 'Billing & Invoice' },
      { key: 'inventory', label: 'Inventory' },
      { key: 'pharmacy', label: 'Pharmacy' },
      { key: 'laboratory', label: 'Laboratory' },
      { key: 'radiology', label: 'Radiology' },
    ],
  },
  {
    title: 'Management',
    permissions: [
      { key: 'doctors', label: 'Doctors' },
      { key: 'employees', label: 'Employees' },
      { key: 'branches', label: 'Branches' },
      { key: 'organization', label: 'Organization' },
      { key: 'users', label: 'Users & Roles' },
      { key: 'crm', label: 'CRM' },
    ],
  },
  {
    title: 'Reports & Integration',
    permissions: [
      { key: 'reports', label: 'Reports' },
      { key: 'ai', label: 'AI Assistant' },
      { key: 'integrations', label: 'Integrations' },
    ],
  },
  {
    title: 'System',
    permissions: [
      { key: 'subscription', label: 'Subscription' },
      { key: 'settings', label: 'Settings' },
    ],
  },
]

function load(): void {
  try { users.value = JSON.parse(localStorage.getItem('users_roles_data') || '[]') } catch { users.value = [] }
}
function save(): void { localStorage.setItem('users_roles_data', JSON.stringify(users.value)) }

onMounted(load)

const filtered = computed(() => {
  if (!search.value) return users.value
  const q = search.value.toLowerCase()
  return users.value.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q) || u.role.includes(q))
})

function openCreate(): void {
  form.value = { ...emptyForm(), id: crypto.randomUUID() }
  formPermissions.value = new Set<string>()
  editing.value = false
  showModal.value = true
  saveMsg.value = ''
}

function openEdit(item: UserRecord): void {
  form.value = { ...item }
  formPermissions.value = new Set<string>(item.permissions ?? [])
  editing.value = true
  showModal.value = true
  saveMsg.value = ''
}

function togglePerm(key: string): void {
  const next = new Set(formPermissions.value)
  if (next.has(key)) next.delete(key)
  else next.add(key)
  formPermissions.value = next
}

function selectAllGroup(group: PermGroup): void {
  const next = new Set(formPermissions.value)
  group.permissions.forEach(p => next.add(p.key))
  formPermissions.value = next
}

function deselectAllGroup(group: PermGroup): void {
  const next = new Set(formPermissions.value)
  group.permissions.forEach(p => next.delete(p.key))
  formPermissions.value = next
}

function selectAll(): void {
  const all = permissionGroups.flatMap(g => g.permissions.map(p => p.key))
  formPermissions.value = new Set(all)
}

function deselectAll(): void {
  formPermissions.value = new Set()
}

function groupCheckedCount(group: PermGroup): number {
  return group.permissions.filter(p => formPermissions.value.has(p.key)).length
}

function groupTotalCount(group: PermGroup): number {
  return group.permissions.length
}

function handleSave(): void {
  if (!form.value.name || !form.value.email) { saveMsg.value = 'Nama & email wajib diisi.'; return }
  if (!editing.value && users.value.some(u => u.email === form.value.email)) { saveMsg.value = 'Email sudah terdaftar.'; return }
  form.value.permissions = [...formPermissions.value]
  if (editing.value) {
    const idx = users.value.findIndex(u => u.id === form.value.id)
    if (idx >= 0) users.value[idx] = { ...form.value }
  } else {
    users.value.unshift({ ...form.value })
  }
  save(); showModal.value = false; saveMsg.value = ''
}

function handleDelete(id: string): void {
  if (!confirm('Hapus user ini?')) return
  users.value = users.value.filter(u => u.id !== id)
  save()
}
</script>

<template>
  <div>
    <div class="mp-head">
      <div>
        <h1 class="mp-title">Users & Roles</h1>
        <p class="mp-desc">Manajemen pengguna, peran (RBAC), dan permission granular.</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Tambah User</button>
    </div>
    <div class="mp-toolbar">
      <div class="mp-search-wrap">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" class="mp-search-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        <input v-model="search" type="text" placeholder="Cari user..." class="mp-search" />
      </div>
    </div>
    <div v-if="!filtered.length" class="mp-empty">
      <div class="mp-empty-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" /></svg></div>
      <h3>Belum ada user</h3>
      <p>Klik "Tambah User" untuk menambahkan data baru.</p>
    </div>
    <div v-else class="mp-table-wrap">
      <table class="mp-table">
        <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Permissions</th><th style="width:100px">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="u in filtered" :key="u.id">
            <td>{{ u.name }}</td>
            <td>{{ u.email }}</td>
            <td><span class="badge" :class="'badge-' + (u.role === 'super_admin' ? 'red' : u.role === 'admin' ? 'blue' : u.role === 'doctor' ? 'cyan' : u.role === 'receptionist' ? 'yellow' : 'gray')">{{ u.role }}</span></td>
            <td><span class="badge" :class="u.status === 'active' ? 'badge-green' : 'badge-gray'">{{ u.status }}</span></td>
            <td><span class="perm-count">{{ (u.permissions ?? []).length }}/{{ permissionGroups.flatMap(g => g.permissions).length }} modul</span></td>
            <td>
              <button class="btn btn-sm btn-ghost" @click="openEdit(u)" title="Edit">✎</button>
              <button class="btn btn-sm btn-danger" @click="handleDelete(u.id)" title="Hapus"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal perm-modal">
          <div class="modal-head">
            <h3>{{ editing ? 'Edit' : 'Tambah' }} User{{ editing ? '' : ' — Pilih Permission' }}</h3>
            <button class="modal-close" @click="showModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <!-- Basic Info -->
            <div class="perm-info-grid">
              <div class="mp-field"><label>Nama <span class="mp-req">*</span></label><input v-model="form.name" class="mp-input" /></div>
              <div class="mp-field"><label>Email <span class="mp-req">*</span></label><input v-model="form.email" type="email" class="mp-input" /></div>
              <div class="mp-field"><label>Role</label><select v-model="form.role" class="mp-input"><option v-for="r in roles" :key="r" :value="r">{{ r.replace('_', ' ') }}</option></select></div>
              <div class="mp-field"><label>Status</label><select v-model="form.status" class="mp-input"><option>active</option><option>inactive</option></select></div>
              <div class="mp-field"><label>Telepon</label><input v-model="form.phone" class="mp-input" /></div>
            </div>

            <!-- Global select/deselect -->
            <div class="perm-global-actions">
              <span class="perm-section-title">Permission Checklist</span>
              <div class="perm-global-btns">
                <button type="button" class="perm-link" @click="selectAll">Pilih Semua</button>
                <button type="button" class="perm-link perm-link-off" @click="deselectAll">Hapus Semua</button>
              </div>
            </div>

            <!-- Permission Checklist -->
            <div v-for="group in permissionGroups" :key="group.title" class="perm-group">
              <div class="perm-group-head">
                <span class="perm-group-name">{{ group.title }}</span>
                <span class="perm-group-count">{{ groupCheckedCount(group) }}/{{ groupTotalCount(group) }}</span>
                <div class="perm-group-actions">
                  <button type="button" class="perm-link" @click="selectAllGroup(group)">Pilih</button>
                  <button type="button" class="perm-link perm-link-off" @click="deselectAllGroup(group)">Hapus</button>
                </div>
              </div>
              <div class="perm-group-grid">
                <label
                  v-for="p in group.permissions"
                  :key="p.key"
                  class="perm-check"
                  :class="{ checked: formPermissions.has(p.key) }"
                >
                  <input
                    type="checkbox"
                    :checked="formPermissions.has(p.key)"
                    class="perm-checkbox"
                    @change="togglePerm(p.key)"
                  />
                  <span class="perm-check-icon">
                    <svg v-if="formPermissions.has(p.key)" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                  </span>
                  <span class="perm-check-label">{{ p.label }}</span>
                </label>
              </div>
            </div>

            <div v-if="saveMsg" :class="saveMsg.includes('wajib') || saveMsg.includes('sudah') ? 'alert alert-error' : 'alert alert-success'" style="margin-top:1rem">{{ saveMsg }}</div>
          </div>
          <div class="modal-foot">
            <button class="btn btn-ghost" @click="showModal = false">Batal</button>
            <button class="btn btn-primary" @click="handleSave">{{ editing ? 'Simpan Perubahan' : 'Simpan User' }}</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.perm-modal { max-width: 720px; }
.perm-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; margin-bottom: 0.5rem; }
.perm-info-grid .mp-field:last-child { grid-column: 1 / -1; }

.perm-section-title { font-size: 0.875rem; font-weight: 700; color: #262626; }
.perm-global-actions { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; padding: 0.625rem 0.75rem; background: #fafafa; border-radius: 8px; border: 1px solid #f0f0f0; }
.perm-global-btns { display: flex; gap: 0.5rem; }
.perm-link { font-size: 0.75rem; font-weight: 600; color: #1890ff; background: none; border: none; cursor: pointer; font-family: inherit; }
.perm-link:hover { text-decoration: underline; }
.perm-link-off { color: #bfbfbf; }
.perm-link-off:hover { color: #f5222d; }

.perm-group { margin-bottom: 1rem; border: 1px solid #f0f0f0; border-radius: 8px; overflow: hidden; }
.perm-group-head { display: flex; align-items: center; gap: 0.625rem; padding: 0.5rem 0.75rem; background: #fafafa; border-bottom: 1px solid #f0f0f0; }
.perm-group-name { font-size: 0.8125rem; font-weight: 700; color: #595959; text-transform: uppercase; letter-spacing: 0.04em; }
.perm-group-count { font-size: 0.75rem; color: #bfbfbf; }
.perm-group-actions { margin-left: auto; display: flex; gap: 0.5rem; }
.perm-group-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.375rem; padding: 0.625rem 0.75rem; }

.perm-check { display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.5rem; border-radius: 6px; cursor: pointer; transition: background 0.15s; font-size: 0.8125rem; color: #595959; }
.perm-check:hover { background: #f5f5f5; }
.perm-check.checked { color: #1890ff; background: #e6f7ff; }
.perm-checkbox { position: absolute; opacity: 0; width: 0; height: 0; }
.perm-check-icon { width: 18px; height: 18px; border-radius: 4px; border: 1.5px solid #d9d9d9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
.perm-check.checked .perm-check-icon { background: #1890ff; border-color: #1890ff; color: #fff; }
.perm-check-label { user-select: none; }

.perm-count { font-size: 0.8125rem; color: #8c8c8c; }

@media (max-width: 640px) {
  .perm-info-grid { grid-template-columns: 1fr; }
  .perm-group-grid { grid-template-columns: 1fr; }
  .perm-info-grid .mp-field:last-child { grid-column: 1; }
}
</style>