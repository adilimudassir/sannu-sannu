<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\'\.]+$/', // Only letters, spaces, hyphens, apostrophes, and periods
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                // 'email:rfc,dns',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => [
                'required',
                'confirmed',
                // Rules\Password::defaults()
                //     ->min(8)
                //     ->letters()
                //     ->mixedCase()
                //     ->numbers()
                //     ->symbols()
                //     ->uncompromised(),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'An account with this email address already exists.',
            'password.uncompromised' => 'The given password has appeared in a data leak. Please choose a different password.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->sanitizeName($this->name),
            'email' => $this->sanitizeEmail($this->email),
        ]);
    }

    /**
     * Sanitize the name input.
     */
    private function sanitizeName(?string $name): ?string
    {
        if (!$name) {
            return $name;
        }

        // Remove extra whitespace and trim
        $name = preg_replace('/\s+/', ' ', trim($name));

        // Remove any potentially harmful characters while preserving international names
        $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);

        return $name;
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
