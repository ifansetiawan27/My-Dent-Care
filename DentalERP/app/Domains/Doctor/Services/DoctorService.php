<?php
declare(strict_types=1);
namespace App\Domains\Doctor\Services;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Doctor\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class DoctorService {
    public function paginate(array $f): LengthAwarePaginator {
        $q = Doctor::where('organization_id',$f['organization_id']);
        if (!empty($f['branch_id'])) $q->where('branch_id',$f['branch_id']);
        if (!empty($f['specialty_id'])) $q->where('specialty_id',$f['specialty_id']);
        if (!empty($f['search'])) $q->where(fn($qq)=>$qq->where('full_name','ILIKE',"%{$f['search']}%")->orWhere('doctor_code','ILIKE',"%{$f['search']}%"));
        if (isset($f['is_active'])) $q->where('is_active',$f['is_active']);
        $s = in_array($f['sort_by']??'',['full_name','doctor_code','hire_date'])?$f['sort_by']:'full_name';
        return $q->orderBy($s,$f['sort_dir']??'asc')->paginate(min((int)($f['per_page']??20),100));
    }
    public function findById(string $id, string $orgId): Doctor {
        $d = Doctor::where('id',$id)->where('organization_id',$orgId)->first();
        if(!$d) throw new NotFoundException("Doctor not found.");
        return $d;
    }
    public function create(array $d): Doctor {
        if(Doctor::where('doctor_code',$d['doctor_code'])->exists()) throw new BusinessException("Doctor code already taken.");
        return DB::transaction(fn()=>Doctor::create($d));
    }
    public function update(string $id, array $d, string $orgId): Doctor {
        $doc = $this->findById($id,$orgId);
        if(isset($d['doctor_code']) && Doctor::where('doctor_code',$d['doctor_code'])->where('id','!=',$id)->exists()) throw new BusinessException("Doctor code already taken.");
        DB::transaction(fn()=>$doc->update($d));
        return $doc->refresh();
    }
    public function delete(string $id, string $orgId): bool { return (bool)$this->findById($id,$orgId)->delete(); }
    public function toggleActive(string $id, string $orgId): Doctor { $d=$this->findById($id,$orgId); $d->update(['is_active'=>!$d->is_active]); return $d->refresh(); }
}
