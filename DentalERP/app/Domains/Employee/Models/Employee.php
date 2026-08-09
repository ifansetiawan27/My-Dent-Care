<?php
declare(strict_types=1);
namespace App\Domains\Employee\Models;
use App\Core\Base\BaseModel;
class Employee extends BaseModel { protected $table = 'employees'; protected $fillable = ['employee_code','full_name','organization_id','branch_id','employment_status','hire_date','resignation_date','position','gender','religion','marital_status','nationality_id','phone','email','address','district_id','village_id','is_active','created_by','updated_by','deleted_by']; protected $casts = ['hire_date'=>'date','resignation_date'=>'date','is_active'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime']; public function organization() { return $this->belongsTo(\App\Domains\Organization\Models\Organization::class); } public function branch() { return $this->belongsTo(\App\Domains\Branch\Models\Branch::class); } }
