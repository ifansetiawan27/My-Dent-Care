<?php
declare(strict_types=1);
namespace App\Domains\Doctor\Models;
use App\Core\Base\BaseModel;
class Doctor extends BaseModel { protected $table = 'doctors'; protected $fillable = ['doctor_code','full_name','organization_id','branch_id','specialty_id','license_number','consultation_fee','gender','religion','marital_status','nationality_id','phone','email','address','district_id','village_id','hire_date','resignation_date','is_active','created_by','updated_by','deleted_by']; protected $casts = ['hire_date'=>'date','resignation_date'=>'date','consultation_fee'=>'decimal:2','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime']; }
