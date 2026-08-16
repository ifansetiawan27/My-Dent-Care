<?php
declare(strict_types=1);
namespace App\Domains\EMR\Requests;
use Illuminate\Foundation\Http\FormRequest;
final class StoreEMRRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['organization_id'=>'required|uuid|exists:organizations,id','patient_id'=>'required|uuid|exists:patients,id','doctor_id'=>'nullable|uuid|exists:doctors,id','appointment_id'=>'nullable|uuid|exists:appointments,id','chief_complaint'=>'nullable|string','diagnosis'=>'nullable|string','treatment_notes'=>'nullable|string','vital_signs'=>'nullable|array','status'=>'sometimes|string|max:20']; } }