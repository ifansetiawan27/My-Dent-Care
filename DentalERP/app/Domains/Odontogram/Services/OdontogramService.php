<?php declare(strict_types=1); namespace App\Domains\Odontogram\Services;
use App\Core\Exceptions\NotFoundException; use App\Domains\Odontogram\Models\Odontogram; use Illuminate\Support\Facades\DB;
final class OdontogramService {
    public function paginate(array $f) { return Odontogram::where('organization_id',$f['organization_id'])->when(!empty($f['patient_id']),fn($q)=>$q->where('patient_id',$f['patient_id']))->orderBy('tooth_number')->paginate(min((int)($f['per_page']??20),100)); }
    public function findById(string $id, string $orgId): Odontogram { $o = Odontogram::where('id',$id)->where('organization_id',$orgId)->first(); if(!$o) throw new NotFoundException("Not found."); return $o; }
    public function create(array $d): Odontogram { return DB::transaction(fn()=>Odontogram::create($d)); }
    public function update(string $id, array $d, string $orgId): Odontogram { $o=$this->findById($id,$orgId); DB::transaction(fn()=>$o->update($d)); return $o->refresh(); }
    public function delete(string $id, string $orgId): bool { return (bool)$this->findById($id,$orgId)->delete(); }
}