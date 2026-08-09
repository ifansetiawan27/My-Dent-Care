<?php
declare(strict_types=1);
namespace App\Domains\Doctor\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
/** @mixin \App\Domains\Doctor\Models\Doctor */
final class DoctorResource extends JsonResource { public function toArray(Request $r): array { return ['id'=>$this->id,'doctor_code'=>$this->doctor_code,'full_name'=>$this->full_name,'organization_id'=>$this->organization_id,'branch_id'=>$this->branch_id,'specialty_id'=>$this->specialty_id,'specialty'=>$this->specialty?->name,'license_number'=>$this->license_number,'consultation_fee'=>$this->consultation_fee,'gender'=>$this->gender,'religion'=>$this->religion,'marital_status'=>$this->marital_status,'nationality_id'=>$this->nationality_id,'phone'=>$this->phone,'email'=>$this->email,'address'=>$this->address,'district_id'=>$this->district_id,'village_id'=>$this->village_id,'hire_date'=>$this->hire_date?->toDateString(),'resignation_date'=>$this->resignation_date?->toDateString(),'is_active'=>$this->is_active,'created_at'=>$this->created_at?->toISOString(),'updated_at'=>$this->updated_at?->toISOString()]; } }
