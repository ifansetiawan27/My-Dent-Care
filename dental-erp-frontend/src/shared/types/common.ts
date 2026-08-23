export type Role = 'super_admin' | 'clinic_owner' | 'dentist' | 'nurse' | 'receptionist';

export interface SelectOption {
  label: string;
  value: string;
}

export interface TableColumn<T> {
  key: keyof T | string;
  label: string;
  sortable?: boolean;
  render?: (value: any, row: T) => React.ReactNode;
}

export interface FilterOption {
  field: string;
  operator: 'eq' | 'ne' | 'gt' | 'gte' | 'lt' | 'lte' | 'like' | 'in';
  value: any;
}

export interface SortOption {
  field: string;
  direction: 'asc' | 'desc';
}
