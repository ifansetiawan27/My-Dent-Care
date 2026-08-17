<?php
declare(strict_types=1);
namespace App\Domains\AI\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domains\AI\Enums\AIStatus;
final class UpdateAIRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['status'=>['sometimes','string',Rule::in(AIStatus::values())],'response'=>'sometimes|string','tokens_used'=>'sometimes|integer|min:0','error_message'=>'sometimes|nullable|string']; } }