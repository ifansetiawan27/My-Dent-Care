export interface Patient {
  id: string;
  name: string;
  email?: string;
  phone?: string;
  date_of_birth?: string;
  gender?: 'male' | 'female' | 'other';
  address?: string;
  clinic_id: string;
  created_at: string;
  updated_at: string;
}

export interface Appointment {
  id: string;
  patient_id: string;
  dentist_id: string;
  clinic_id: string;
  appointment_date: string;
  start_time: string;
  end_time: string;
  status: 'scheduled' | 'confirmed' | 'cancelled' | 'completed';
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface Treatment {
  id: string;
  patient_id: string;
  dentist_id: string;
  clinic_id: string;
  treatment_date: string;
  treatment_type: string;
  description?: string;
  cost: number;
  status: 'planned' | 'in_progress' | 'completed';
  created_at: string;
  updated_at: string;
}

export interface Invoice {
  id: string;
  patient_id: string;
  clinic_id: string;
  invoice_number: string;
  invoice_date: string;
  due_date: string;
  total_amount: number;
  paid_amount: number;
  status: 'draft' | 'sent' | 'paid' | 'overdue';
  created_at: string;
  updated_at: string;
}

export interface InventoryItem {
  id: string;
  clinic_id: string;
  name: string;
  sku?: string;
  category: string;
  quantity: number;
  unit_price: number;
  reorder_level?: number;
  supplier?: string;
  created_at: string;
  updated_at: string;
}
