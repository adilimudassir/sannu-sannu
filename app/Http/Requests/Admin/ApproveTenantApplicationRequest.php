<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApproveTenantApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-platform');
    }

    public function rules(): array
    {
        return [
            'notes' => 'nullable|string',
        ];
    }
}
