<?php
declare(strict_types=1);
namespace App\Domains\Appointment\Requests;
use Illuminate\Foundation\Http\FormRequest;
final class UpdateAppointmentRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['scheduled_at'=>'sometimes|date','end_at'=>'nullable|date|after:scheduled_at','status'=>'sometimes|string|max:20','type'=>'nullable|string|max:50','notes'=>'nullable|string']; } }