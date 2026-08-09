<?php
declare(strict_types=1);
namespace App\Domains\Appointment\Services;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Appointment\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class AppointmentService {
    public function paginate(array $f): LengthAwarePaginator {
        $q = Appointment::with(['patient','doctor'])->where('organization_id',$f['organization_id']);
        if (!empty($f['branch_id'])) $q->where('branch_id',$f['branch_id']);
        if (!empty($f['doctor_id'])) $q->where('doctor_id',$f['doctor_id']);
        if (!empty($f['patient_id'])) $q->where('patient_id',$f['patient_id']);
        if (!empty($f['status'])) $q->where('status',$f['status']);
        if (!empty($f['date_from'])) $q->where('scheduled_at','>=',$f['date_from']);
        if (!empty($f['date_to'])) $q->where('scheduled_at','<=',$f['date_to']);
        return $q->orderBy('scheduled_at','desc')->paginate(min((int)($f['per_page']??20),100));
    }
    public function findById(string $id, string $orgId): Appointment {
        $a = Appointment::with(['patient','doctor'])->where('id',$id)->where('organization_id',$orgId)->first();
        if(!$a) throw new NotFoundException("Appointment not found.");
        return $a;
    }
    public function create(array $d): Appointment { return DB::transaction(fn()=>Appointment::create($d)); }
    public function update(string $id, array $d, string $orgId): Appointment { $a=$this->findById($id,$orgId); DB::transaction(fn()=>$a->update($d)); return $a->refresh()->load(['patient','doctor']); }
    public function delete(string $id, string $orgId): bool { return (bool)$this->findById($id,$orgId)->delete(); }
    public function updateStatus(string $id, string $status, string $orgId): Appointment { $a=$this->findById($id,$orgId); $a->update(['status'=>$status]); return $a->refresh()->load(['patient','doctor']); }
}
