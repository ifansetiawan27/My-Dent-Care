<?php
declare(strict_types=1);
namespace App\Domains\EMR\Models;
use App\Core\Base\BaseModel;
class EMR extends BaseModel { protected $table = 'emrs'; protected $fillable = ['organization_id','patient_id','doctor_id','appointment_id','chief_complaint','diagnosis','treatment_notes','vital_signs','status','created_by','updated_by','deleted_by']; protected $casts = ['vital_signs'=>'array','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime']; public function patient() { return $this->belongsTo(\App\Domains\Patient\Models\Patient::class); } public function doctor() { return $this->belongsTo(\App\Domains\Doctor\Models\Doctor::class); } }
