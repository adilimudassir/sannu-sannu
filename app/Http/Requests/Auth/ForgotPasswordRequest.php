<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.email' => 'Please enter a valid email address.',
            'email.required' => 'Please enter your email address.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->sanitizeEmail($this->email),
        ]);
    }

    /**
     * Sanitize the email input.
     */
    private function sanitizeEmail(?string $email): ?string
    {
        if (!$email) {
            return $email;
        }

        // Trim and convert to lowercase
        $email = strtolower(trim($email));
        
        // Sanitize email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        return $email;
    }
}