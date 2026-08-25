export interface SubscriptionResource {
  id: string
  organization_id: string
  plan: 'starter' | 'professional' | 'enterprise'
  status: 'trial' | 'active' | 'past_due' | 'grace' | 'expired' | 'cancelled'
  status_label: string
  price: number
  trial: Trial | null
  is_trial: boolean
  is_restricted: boolean
  billing: Billing
  storage: Storage
  created_at: string
  updated_at: string
}

export interface Trial {
  start_date: string
  end_date: string
  days_remaining: number
}

export interface Billing {
  current_period_start: string | null
  current_period_end: string | null
  next_billing_at: string | null
  grace_ends_at: string | null
}

export interface Storage {
  limit_gb: number
  used_gb: number
  remaining_gb: number
}

export interface PlanResource {
  code: string
  name: string
  price: number
  storage_gb: number
  branches: number
  trial_days?: number
}