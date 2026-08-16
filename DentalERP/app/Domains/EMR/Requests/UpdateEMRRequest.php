<?php
declare(strict_types=1);
namespace App\Domains\EMR\Requests;
use Illuminate\Foundation\Http\FormRequest;
final class UpdateEMRRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['chief_complaint'=>'sometimes|string','diagnosis'=>'sometimes|string','treatment_notes'=>'sometimes|string','vital_signs'=>'nullable|array','status'=>'sometimes|string|max:20']; } }