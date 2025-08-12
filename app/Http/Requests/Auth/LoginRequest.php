<?php

namespace App\Http\Requests\Auth;

use App\Services\AuditLogService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', 'min:1'],
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

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Log failed login attempt for audit purposes
            AuditLogService::logAuthEvent('login_failed', null, $this, [
                'email' => $this->string('email'),
                'attempts' => RateLimiter::attempts($this->throttleKey()),
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Log successful login for audit purposes
        AuditLogService::logAuthEvent('login_success', Auth::user(), $this);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        // Log account lockout for audit purposes
        AuditLogService::logAuthEvent('login_locked', null, $this, [
            'email' => $this->string('email'),
            'attempts' => RateLimiter::attempts($this->throttleKey()),
        ]);

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
