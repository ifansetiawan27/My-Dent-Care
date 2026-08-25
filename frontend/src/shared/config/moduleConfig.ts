export type FieldType = 'text' | 'number' | 'date' | 'datetime' | 'email' | 'select' | 'textarea' | 'money' | 'lookup'

export interface FieldDef {
  key: string
  label: string
  type: FieldType
  required?: boolean
  placeholder?: string
  options?: string[]
  lookupEndpoint?: string
  lookupLabel?: string
  lookupValue?: string
}

export interface ColumnDef {
  key: string
  label: string
  type?: 'text' | 'date' | 'datetime' | 'money' | 'badge' | 'boolean'
  maxWidth?: string
}

export interface ModuleConfig {
  resource: string
  label: string
  description: string
  api: string
  searchKeys: string[]
  columns: ColumnDef[]
  fields: FieldDef[]
  autoFill?: string[]
}

const statusBadgeColumns: ColumnDef = { key: 'status', label: 'Status', type: 'badge' }
const dateCol: ColumnDef = { key: 'created_at', label: 'Dibuat', type: 'datetime' }

export const moduleConfigs: Record<string, ModuleConfig> = {
  appointments: {
    resource: 'appointments',
    label: 'Appointment',
    description: 'Kelola jadwal janji temu pasien, cegah double-booking, dan pantau status appointment.',
    api: '/v1/appointments',
    searchKeys: ['patient', 'doctor', 'status', 'type'],
    columns: [
      { key: 'patient', label: 'Pasien' },
      { key: 'doctor', label: 'Dokter' },
      { key: 'scheduled_at', label: 'Jadwal', type: 'datetime' },
      { key: 'type', label: 'Tipe' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'patient_id', label: 'Pasien', type: 'lookup', lookupEndpoint: '/v1/patients', lookupLabel: 'full_name', lookupValue: 'id', required: true },
      { key: 'doctor_id', label: 'Dokter', type: 'lookup', lookupEndpoint: '/v1/doctors', lookupLabel: 'full_name', lookupValue: 'id' },
      { key: 'scheduled_at', label: 'Jadwal', type: 'datetime', required: true },
      { key: 'type', label: 'Tipe', type: 'select', options: ['checkup', 'treatment', 'consultation', 'follow_up', 'emergency'] },
      { key: 'status', label: 'Status', type: 'select', options: ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'] },
      { key: 'notes', label: 'Catatan', type: 'textarea' },
    ],
    autoFill: ['organization_id', 'branch_id'],
  },
  patients: {
    resource: 'patients',
    label: 'Pasien',
    description: 'Registrasi pasien, nomor rekam medis otomatis, dan data demografis lengkap.',
    api: '/v1/patients',
    searchKeys: ['patient_code', 'full_name', 'phone', 'email'],
    columns: [
      { key: 'patient_code', label: 'No. RM' },
      { key: 'full_name', label: 'Nama Pasien' },
      { key: 'gender', label: 'Jenis Kelamin' },
      { key: 'birth_date', label: 'Tgl Lahir', type: 'date' },
      { key: 'phone', label: 'Telepon' },
    ],
    fields: [
      { key: 'patient_code', label: 'No. Rekam Medis', type: 'text', required: true, placeholder: 'RM-0001' },
      { key: 'full_name', label: 'Nama Lengkap', type: 'text', required: true },
      { key: 'birth_date', label: 'Tanggal Lahir', type: 'date' },
      { key: 'gender', label: 'Jenis Kelamin', type: 'select', options: ['male', 'female'] },
      { key: 'blood_type', label: 'Gol. Darah', type: 'select', options: ['A', 'B', 'AB', 'O'] },
      { key: 'religion', label: 'Agama', type: 'text' },
      { key: 'marital_status', label: 'Status', type: 'select', options: ['single', 'married', 'divorced', 'widowed'] },
      { key: 'phone', label: 'Telepon', type: 'text' },
      { key: 'email', label: 'Email', type: 'email' },
      { key: 'address', label: 'Alamat', type: 'textarea' },
    ],
    autoFill: ['organization_id', 'branch_id'],
  },
  emrs: {
    resource: 'emrs',
    label: 'Rekam Medis (EMR)',
    description: 'Rekam medis digital pasien, riwayat kunjungan, diagnosa, dan dokumentasi medis.',
    api: '/v1/emrs',
    searchKeys: ['chief_complaint', 'diagnosis', 'status'],
    columns: [
      { key: 'patient', label: 'Pasien' },
      { key: 'chief_complaint', label: 'Keluhan Utama' },
      { key: 'diagnosis', label: 'Diagnosa' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'patient_id', label: 'Pasien', type: 'lookup', lookupEndpoint: '/v1/patients', lookupLabel: 'full_name', lookupValue: 'id', required: true },
      { key: 'doctor_id', label: 'Dokter', type: 'lookup', lookupEndpoint: '/v1/doctors', lookupLabel: 'full_name', lookupValue: 'id' },
      { key: 'appointment_id', label: 'Appointment', type: 'lookup', lookupEndpoint: '/v1/appointments', lookupLabel: 'id', lookupValue: 'id' },
      { key: 'chief_complaint', label: 'Keluhan Utama', type: 'text', required: true },
      { key: 'diagnosis', label: 'Diagnosa', type: 'text' },
      { key: 'treatment_notes', label: 'Catatan Perawatan', type: 'textarea' },
      { key: 'vital_signs', label: 'Tanda Vital (JSON)', type: 'textarea' },
    ],
    autoFill: ['organization_id'],
  },
  odontograms: {
    resource: 'odontograms',
    label: 'Odontogram',
    description: 'Tooth charting digital untuk pemetaan kondisi gigi dan rencana perawatan.',
    api: '/v1/odontograms',
    searchKeys: ['tooth_number', 'condition', 'tooth_type'],
    columns: [
      { key: 'patient', label: 'Pasien' },
      { key: 'tooth_number', label: 'Gigi No.' },
      { key: 'tooth_type', label: 'Tipe' },
      { key: 'surface', label: 'Permukaan' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'patient_id', label: 'Pasien', type: 'lookup', lookupEndpoint: '/v1/patients', lookupLabel: 'full_name', lookupValue: 'id', required: true },
      { key: 'tooth_number', label: 'Nomor Gigi', type: 'number', required: true },
      { key: 'tooth_type', label: 'Tipe Gigi', type: 'select', options: ['permanent', 'deciduous'] },
      { key: 'surface', label: 'Permukaan', type: 'text' },
      { key: 'condition', label: 'Kondisi', type: 'text' },
      { key: 'notes', label: 'Catatan', type: 'textarea' },
    ],
    autoFill: ['organization_id'],
  },
  treatments: {
    resource: 'treatments',
    label: 'Perawatan',
    description: 'Katalog tindakan dental, perhitungan biaya, dan riwayat perawatan pasien.',
    api: '/v1/treatments',
    searchKeys: ['treatment_type', 'patient', 'status'],
    columns: [
      { key: 'patient', label: 'Pasien' },
      { key: 'treatment_type', label: 'Tindakan' },
      { key: 'cost', label: 'Biaya', type: 'money' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'patient_id', label: 'Pasien', type: 'lookup', lookupEndpoint: '/v1/patients', lookupLabel: 'full_name', lookupValue: 'id', required: true },
      { key: 'doctor_id', label: 'Dokter', type: 'lookup', lookupEndpoint: '/v1/doctors', lookupLabel: 'full_name', lookupValue: 'id' },
      { key: 'appointment_id', label: 'Appointment', type: 'lookup', lookupEndpoint: '/v1/appointments', lookupLabel: 'id', lookupValue: 'id' },
      { key: 'treatment_type', label: 'Jenis Tindakan', type: 'text', required: true },
      { key: 'cost', label: 'Biaya (Rp)', type: 'money' },
      { key: 'description', label: 'Deskripsi', type: 'textarea' },
    ],
    autoFill: ['organization_id'],
  },
  invoices: {
    resource: 'invoices',
    label: 'Billing & Invoice',
    description: 'Penagihan pasien, invoice profesional, pembayaran multi-metode, dan PPN.',
    api: '/v1/invoices',
    searchKeys: ['invoice_number', 'patient', 'status'],
    columns: [
      { key: 'invoice_number', label: 'No. Invoice' },
      { key: 'patient', label: 'Pasien' },
      { key: 'total_amount', label: 'Total', type: 'money' },
      { key: 'due_date', label: 'Jatuh Tempo', type: 'date' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'patient_id', label: 'Pasien', type: 'lookup', lookupEndpoint: '/v1/patients', lookupLabel: 'full_name', lookupValue: 'id', required: true },
      { key: 'invoice_number', label: 'No. Invoice', type: 'text' },
      { key: 'total_amount', label: 'Total (Rp)', type: 'money', required: true },
      { key: 'due_date', label: 'Jatuh Tempo', type: 'date' },
      { key: 'status', label: 'Status', type: 'select', options: ['draft', 'unpaid', 'partially_paid', 'paid', 'overdue', 'void'] },
      { key: 'notes', label: 'Catatan', type: 'textarea' },
    ],
    autoFill: ['organization_id'],
  },
  inventory_items: {
    resource: 'inventory_items',
    label: 'Inventaris',
    description: 'Stock level per cabang, reorder point otomatis, dan transfer stok antar cabang.',
    api: '/v1/inventory-items',
    searchKeys: ['item_code', 'name', 'category'],
    columns: [
      { key: 'item_code', label: 'Kode' },
      { key: 'name', label: 'Nama Barang' },
      { key: 'quantity', label: 'Qty' },
      { key: 'unit', label: 'Satuan' },
      { key: 'unit_price', label: 'Harga', type: 'money' },
      { key: 'is_active', label: 'Aktif', type: 'boolean' },
    ],
    fields: [
      { key: 'item_code', label: 'Kode Barang', type: 'text', required: true },
      { key: 'name', label: 'Nama Barang', type: 'text', required: true },
      { key: 'quantity', label: 'Jumlah', type: 'number' },
      { key: 'min_quantity', label: 'Min. Stok', type: 'number' },
      { key: 'unit', label: 'Satuan', type: 'text' },
      { key: 'unit_price', label: 'Harga Satuan (Rp)', type: 'money' },
      { key: 'is_active', label: 'Aktif', type: 'select', options: ['1', '0'] },
    ],
    autoFill: ['organization_id', 'branch_id'],
  },
  pharmacy_items: {
    resource: 'pharmacy_items',
    label: 'Farmasi',
    description: 'Kelola obat & bahan farmasi, batch & expiry date tracking, dan resep digital.',
    api: '/v1/pharmacy-items',
    searchKeys: ['drug_code', 'name', 'category', 'batch_number'],
    columns: [
      { key: 'drug_code', label: 'Kode Obat' },
      { key: 'name', label: 'Nama Obat' },
      { key: 'quantity', label: 'Qty' },
      { key: 'unit', label: 'Satuan' },
      { key: 'expiry_date', label: 'Kedaluwarsa', type: 'date' },
      { key: 'is_active', label: 'Aktif', type: 'boolean' },
    ],
    fields: [
      { key: 'drug_code', label: 'Kode Obat', type: 'text', required: true },
      { key: 'name', label: 'Nama Obat', type: 'text', required: true },
      { key: 'quantity', label: 'Jumlah', type: 'number' },
      { key: 'unit', label: 'Satuan', type: 'text' },
      { key: 'unit_price', label: 'Harga (Rp)', type: 'money' },
      { key: 'batch_number', label: 'No. Batch', type: 'text' },
      { key: 'expiry_date', label: 'Kedaluwarsa', type: 'date' },
      { key: 'is_active', label: 'Aktif', type: 'select', options: ['1', '0'] },
    ],
    autoFill: ['organization_id', 'branch_id'],
  },
  lab_orders: {
    resource: 'lab_orders',
    label: 'Laboratorium',
    description: 'Order lab, kategori pemeriksaan, dan hasil laboratorium pasien.',
    api: '/v1/lab-orders',
    searchKeys: ['order_number', 'patient', 'status'],
    columns: [
      { key: 'order_number', label: 'No. Order' },
      { key: 'patient', label: 'Pasien' },
      { key: 'doctor', label: 'Dokter' },
      statusBadgeColumns,
      { key: 'completed_at', label: 'Selesai', type: 'datetime' },
    ],
    fields: [
      { key: 'patient_id', label: 'Pasien', type: 'lookup', lookupEndpoint: '/v1/patients', lookupLabel: 'full_name', lookupValue: 'id', required: true },
      { key: 'doctor_id', label: 'Dokter', type: 'lookup', lookupEndpoint: '/v1/doctors', lookupLabel: 'full_name', lookupValue: 'id' },
      { key: 'order_number', label: 'No. Order', type: 'text' },
      { key: 'status', label: 'Status', type: 'select', options: ['ordered', 'processing', 'completed', 'cancelled'] },
      { key: 'description', label: 'Deskripsi', type: 'textarea' },
      { key: 'results', label: 'Hasil', type: 'textarea' },
    ],
    autoFill: ['organization_id'],
  },
  doctors: {
    resource: 'doctors',
    label: 'Dokter',
    description: 'Profil dokter, spesialisasi, jadwal praktik, dan produktivitas.',
    api: '/v1/doctors',
    searchKeys: ['doctor_code', 'full_name', 'license_number', 'specialty'],
    columns: [
      { key: 'doctor_code', label: 'Kode' },
      { key: 'full_name', label: 'Nama Dokter' },
      { key: 'specialty', label: 'Spesialisasi' },
      { key: 'consultation_fee', label: 'Biaya Konsultasi', type: 'money' },
      { key: 'phone', label: 'Telepon' },
    ],
    fields: [
      { key: 'doctor_code', label: 'Kode Dokter', type: 'text', required: true },
      { key: 'full_name', label: 'Nama Lengkap', type: 'text', required: true },
      { key: 'license_number', label: 'No. STR/SIP', type: 'text' },
      { key: 'consultation_fee', label: 'Biaya Konsultasi (Rp)', type: 'money' },
      { key: 'phone', label: 'Telepon', type: 'text' },
      { key: 'email', label: 'Email', type: 'email' },
      { key: 'gender', label: 'Jenis Kelamin', type: 'select', options: ['male', 'female'] },
    ],
    autoFill: ['organization_id', 'branch_id'],
  },
  employees: {
    resource: 'employees',
    label: 'Karyawan',
    description: 'Data kepegawaian, jabatan, dan manajemen tim klinik Anda.',
    api: '/v1/employees',
    searchKeys: ['employee_code', 'full_name', 'position', 'employment_status'],
    columns: [
      { key: 'employee_code', label: 'NIP' },
      { key: 'full_name', label: 'Nama' },
      { key: 'position', label: 'Jabatan' },
      { key: 'employment_status', label: 'Status Kerja', type: 'badge' },
      { key: 'hire_date', label: 'Mulai Kerja', type: 'date' },
    ],
    fields: [
      { key: 'employee_code', label: 'NIP', type: 'text', required: true },
      { key: 'full_name', label: 'Nama Lengkap', type: 'text', required: true },
      { key: 'employment_status', label: 'Status Kerja', type: 'select', required: true, options: ['active', 'contract', 'probation', 'resigned', 'terminated'] },
      { key: 'hire_date', label: 'Tanggal Masuk', type: 'date', required: true },
      { key: 'position', label: 'Jabatan', type: 'text' },
      { key: 'phone', label: 'Telepon', type: 'text' },
      { key: 'email', label: 'Email', type: 'email' },
    ],
    autoFill: ['organization_id', 'branch_id'],
  },
  branches: {
    resource: 'branches',
    label: 'Cabang',
    description: 'Kelola multi-cabang klinik dengan data terpusat dan hak akses per cabang.',
    api: '/v1/branches',
    searchKeys: ['branch_code', 'branch_name', 'city', 'phone'],
    columns: [
      { key: 'branch_code', label: 'Kode' },
      { key: 'branch_name', label: 'Nama Cabang' },
      { key: 'branch_type', label: 'Tipe' },
      { key: 'city', label: 'Kota' },
      { key: 'phone', label: 'Telepon' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'branch_code', label: 'Kode Cabang', type: 'text', required: true },
      { key: 'branch_name', label: 'Nama Cabang', type: 'text', required: true },
      { key: 'branch_type', label: 'Tipe', type: 'select', required: true, options: ['main', 'branch'] },
      { key: 'phone', label: 'Telepon', type: 'text', required: true },
      { key: 'email', label: 'Email', type: 'email' },
      { key: 'address', label: 'Alamat', type: 'textarea' },
      { key: 'city', label: 'Kota', type: 'text' },
      { key: 'province', label: 'Provinsi', type: 'text' },
      { key: 'postal_code', label: 'Kode Pos', type: 'text' },
      { key: 'timezone', label: 'Zona Waktu', type: 'text' },
      { key: 'status', label: 'Status', type: 'select', options: ['active', 'inactive'] },
    ],
    autoFill: ['organization_id'],
  },
  crm_contacts: {
    resource: 'crm_contacts',
    label: 'CRM',
    description: 'Kontak, follow-up pasien, dan campaign komunikasi klinik.',
    api: '/v1/crm-contacts',
    searchKeys: ['contact_type', 'channel', 'subject', 'status'],
    columns: [
      { key: 'patient', label: 'Pasien' },
      { key: 'contact_type', label: 'Tipe Kontak' },
      { key: 'channel', label: 'Kanal' },
      { key: 'subject', label: 'Subjek' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'patient_id', label: 'Pasien', type: 'lookup', lookupEndpoint: '/v1/patients', lookupLabel: 'full_name', lookupValue: 'id' },
      { key: 'contact_type', label: 'Tipe Kontak', type: 'text' },
      { key: 'channel', label: 'Kanal', type: 'select', options: ['whatsapp', 'email', 'sms', 'call'] },
      { key: 'subject', label: 'Subjek', type: 'text' },
      { key: 'message', label: 'Pesan', type: 'textarea' },
      { key: 'status', label: 'Status', type: 'select', options: ['new', 'contacted', 'follow_up', 'closed'] },
    ],
    autoFill: ['organization_id', 'branch_id'],
  },
  reports: {
    resource: 'reports',
    label: 'Laporan',
    description: 'Laporan keuangan, kunjungan, dan analitik operasional klinik.',
    api: '/v1/reports',
    searchKeys: ['name', 'report_type', 'status'],
    columns: [
      { key: 'name', label: 'Nama Laporan' },
      { key: 'report_type', label: 'Tipe' },
      { key: 'report_date', label: 'Tanggal', type: 'date' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'name', label: 'Nama Laporan', type: 'text', required: true },
      { key: 'report_type', label: 'Tipe', type: 'select', options: ['revenue', 'visit', 'patient', 'doctor', 'finance', 'inventory'] },
      { key: 'report_date', label: 'Tanggal', type: 'date' },
      { key: 'parameters', label: 'Parameter (JSON)', type: 'textarea' },
    ],
    autoFill: ['organization_id'],
  },
  ai_queries: {
    resource: 'ai_queries',
    label: 'AI Assistant',
    description: 'AI-powered diagnosis assistance dan predictive analytics untuk klinik Anda.',
    api: '/v1/ai-queries',
    searchKeys: ['query_type', 'prompt', 'status', 'model'],
    columns: [
      { key: 'query_type', label: 'Tipe Query' },
      { key: 'prompt', label: 'Prompt', maxWidth: '280px' },
      { key: 'model', label: 'Model' },
      { key: 'tokens_used', label: 'Tokens' },
      statusBadgeColumns,
    ],
    fields: [
      { key: 'query_type', label: 'Tipe Query', type: 'select', options: ['diagnosis_assist', 'risk_alert', 'no_show_prediction', 'general'] },
      { key: 'prompt', label: 'Prompt', type: 'textarea', required: true },
    ],
    autoFill: ['organization_id'],
  },
  integration_configs: {
    resource: 'integration_configs',
    label: 'Integrasi',
    description: 'SATUSEHAT, BPJS, Midtrans, dan integrasi pihak ketiga lainnya.',
    api: '/v1/integration-configs',
    searchKeys: ['provider', 'name', 'status'],
    columns: [
      { key: 'provider', label: 'Provider' },
      { key: 'name', label: 'Nama' },
      { key: 'is_active', label: 'Aktif', type: 'boolean' },
      { key: 'last_sync_at', label: 'Sync Terakhir', type: 'datetime' },
    ],
    fields: [
      { key: 'provider', label: 'Provider', type: 'text', required: true },
      { key: 'name', label: 'Nama Konfigurasi', type: 'text', required: true },
      { key: 'is_active', label: 'Aktif', type: 'select', options: ['1', '0'] },
    ],
    autoFill: ['organization_id'],
  },
}

export function getModuleConfig(key: string): ModuleConfig | undefined {
  return moduleConfigs[key]
}
