<?php
declare(strict_types=1);
namespace App\Domains\Odontogram\Requests;
use Illuminate\Foundation\Http\FormRequest;
final class UpdateOdontogramRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['tooth_type'=>'sometimes|string|max:20','surface'=>'sometimes|string|max:50','condition'=>'sometimes|string|max:50','notes'=>'nullable|string','findings'=>'nullable|array']; } }