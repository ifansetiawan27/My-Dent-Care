<?php
declare(strict_types=1);
namespace App\Domains\EMR\Services;
use App\Core\Exceptions\NotFoundException;
use App\Domains\EMR\Models\EMR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EMRService {
    public function paginate(array $f): LengthAwarePaginator {
        $q = EMR::with(['patient','doctor'])->where('organization_id',$f['organization_id']);
        if (!empty($f['patient_id'])) $q->where('patient_id',$f['patient_id']);
        if (!empty($f['doctor_id'])) $q->where('doctor_id',$f['doctor_id']);
        if (!empty($f['status'])) $q->where('status',$f['status']);
        return $q->orderBy('created_at','desc')->paginate(min((int)($f['per_page']??20),100));
    }
    public function findById(string $id, string $orgId): EMR { $e = EMR::with(['patient','doctor'])->where('id',$id)->where('organization_id',$orgId)->first(); if(!$e) throw new NotFoundException("EMR not found."); return $e; }
    public function create(array $d): EMR { return DB::transaction(fn()=>EMR::create($d)); }
    public function update(string $id, array $d, string $orgId): EMR { $e=$this->findById($id,$orgId); DB::transaction(fn()=>$e->update($d)); return $e->refresh()->load(['patient','doctor']); }
    public function delete(string $id, string $orgId): bool { return (bool)$this->findById($id,$orgId)->delete(); }
}
