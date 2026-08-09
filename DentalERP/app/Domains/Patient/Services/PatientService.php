<?php
declare(strict_types=1);
namespace App\Domains\Patient\Services;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class PatientService {
    public function paginate(array $f): LengthAwarePaginator {
        $q = Patient::where('organization_id',$f['organization_id']);
        if (!empty($f['branch_id'])) $q->where('branch_id',$f['branch_id']);
        if (!empty($f['patient_type_id'])) $q->where('patient_type_id',$f['patient_type_id']);
        if (!empty($f['search'])) $q->where(fn($qq)=>$qq->where('full_name','ILIKE',"%{$f['search']}%")->orWhere('patient_code','ILIKE',"%{$f['search']}%"));
        if (isset($f['is_active'])) $q->where('is_active',$f['is_active']);
        $s = in_array($f['sort_by']??'',['full_name','patient_code','birth_date'])?$f['sort_by']:'full_name';
        return $q->orderBy($s,$f['sort_dir']??'asc')->paginate(min((int)($f['per_page']??20),100));
    }
    public function findById(string $id, string $orgId): Patient {
        $p = Patient::where('id',$id)->where('organization_id',$orgId)->first();
        if(!$p) throw new NotFoundException("Patient not found.");
        return $p;
    }
    public function create(array $d): Patient {
        if(Patient::where('patient_code',$d['patient_code'])->exists()) throw new BusinessException("Patient code already taken.");
        return DB::transaction(fn()=>Patient::create($d));
    }
    public function update(string $id, array $d, string $orgId): Patient {
        $p = $this->findById($id,$orgId);
        if(isset($d['patient_code']) && Patient::where('patient_code',$d['patient_code'])->where('id','!=',$id)->exists()) throw new BusinessException("Patient code already taken.");
        DB::transaction(fn()=>$p->update($d));
        return $p->refresh();
    }
    public function delete(string $id, string $orgId): bool { return (bool)$this->findById($id,$orgId)->delete(); }
    public function toggleActive(string $id, string $orgId): Patient { $p=$this->findById($id,$orgId); $p->update(['is_active'=>!$p->is_active]); return $p->refresh(); }
}
