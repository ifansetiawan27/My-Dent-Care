<?php
declare(strict_types=1);
namespace App\Domains\AI\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domains\AI\Enums\AIStatus;
final class StoreAIRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['query_type'=>'required|string|max:50','prompt'=>'required|string','model'=>'nullable|string|max:50']; } }