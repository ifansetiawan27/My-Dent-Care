<?php
declare(strict_types=1);
namespace App\Domains\Patient\Models;
use App\Core\Base\BaseModel;
class Patient extends BaseModel { protected $table = 'patients'; protected $fillable = ['patient_code','full_name','birth_date','gender','blood_type','religion','marital_status','nationality_id','patient_type_id','organization_id','branch_id','phone','email','address','district_id','village_id','is_active','created_by','updated_by','deleted_by']; protected $casts = ['birth_date'=>'date','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime']; }
