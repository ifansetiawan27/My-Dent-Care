<?php
declare(strict_types=1);
namespace App\Domains\Employee\Services;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Enums\EmploymentStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EmployeeService {
    public function paginate(array $filters): LengthAwarePaginator {
        $query = Employee::query()->where('organization_id', $filters['organization_id']);
        if (!empty($filters['branch_id'])) $query->where('branch_id', $filters['branch_id']);
        if (!empty($filters['search'])) $query->where(function($q) use ($filters) { $q->where('full_name','ILIKE',"%{$filters['search']}%")->orWhere('employee_code','ILIKE',"%{$filters['search']}%"); });
        if (isset($filters['is_active'])) $query->where('is_active', $filters['is_active']);
        $sortBy = in_array($filters['sort_by']??'', ['full_name','employee_code','hire_date']) ? $filters['sort_by'] : 'full_name';
        return $query->orderBy($sortBy, $filters['sort_dir']??'asc')->paginate(min((int)($filters['per_page']??20), 100));
    }
    public function findById(string $id, string $organizationId): Employee {
        $e = Employee::where('id',$id)->where('organization_id',$organizationId)->first();
        if (!$e) throw new NotFoundException("Employee not found.");
        return $e;
    }
    public function create(array $data): Employee {
        if (Employee::where('employee_code',$data['employee_code'])->exists()) throw new BusinessException("Employee code already taken.");
        return DB::transaction(fn() => Employee::create($data));
    }
    public function update(string $id, array $data, string $organizationId): Employee {
        $e = $this->findById($id, $organizationId);
        if (isset($data['employee_code']) && Employee::where('employee_code',$data['employee_code'])->where('id','!=',$id)->exists()) throw new BusinessException("Employee code already taken.");
        if (isset($data['branch_id']) && $data['branch_id']) { $branch = \App\Domains\Branch\Models\Branch::find($data['branch_id']); if (!$branch || $branch->organization_id !== $e->organization_id) throw new BusinessException("Branch must belong to the same organization."); }
        DB::transaction(fn() => $e->update($data));
        return $e->refresh();
    }
    public function delete(string $id, string $organizationId): bool {
        return (bool) $this->findById($id, $organizationId)->delete();
    }
    public function toggleActive(string $id, string $organizationId): Employee {
        $e = $this->findById($id, $organizationId);
        $e->update(['is_active' => !$e->is_active]);
        return $e->refresh();
    }
}
