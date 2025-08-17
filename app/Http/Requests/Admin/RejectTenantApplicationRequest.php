<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectTenantApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-platform');
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
