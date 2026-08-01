<?php

declare(strict_types=1);

namespace App\Core\Base;

use Illuminate\Foundation\Http\FormRequest;

/**
 * BaseRequest
 *
 * Abstract base class for all FormRequests in the application.
 * Provides a default authorize() implementation that checks authentication.
 * Override authorize() in child classes for fine-grained permission control.
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Defaults to authenticated user only.
     * Override in child classes for role/permission-based authorization.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
