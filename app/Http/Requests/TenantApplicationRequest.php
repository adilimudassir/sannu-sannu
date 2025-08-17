<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantApplicationRequest extends FormRequest
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
            'organization_name' => [
                'required',
                'string',
                'max:255',
                'unique:tenant_applications,organization_name',
                'regex:/^[a-zA-Z0-9\s\-\&\.\,\']+$/', // Allow letters, numbers, spaces, hyphens, ampersands, periods, commas, apostrophes
            ],
            'business_description' => [
                'required',
                'string',
                'min:50',
                'max:1000',
            ],
            'industry_type' => [
                'required',
                'string',
                'in:'.implode(',', array_keys($this->getIndustryTypes())),
            ],
            'contact_person_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\'\.]+$/', // Only letters, spaces, hyphens, apostrophes, and periods
            ],
            'contact_person_email' => [
                'required',
                'email',
                'max:255',
            ],
            'contact_person_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/', // Phone number format
            ],
            'business_registration_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'website_url' => [
                'nullable',
                'url',
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
            'organization_name.required' => 'Organization name is required.',
            'organization_name.unique' => 'An organization with this name already exists.',
            'organization_name.regex' => 'Organization name may only contain letters, numbers, spaces, hyphens, ampersands, periods, commas, and apostrophes.',
            'business_description.required' => 'Business description is required.',
            'business_description.min' => 'Please provide a more detailed business description (at least 50 characters).',
            'business_description.max' => 'Business description cannot exceed 1000 characters.',
            'industry_type.required' => 'Please select an industry type.',
            'industry_type.in' => 'Please select a valid industry type.',
            'contact_person_name.required' => 'Contact person name is required.',
            'contact_person_name.regex' => 'Contact person name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'contact_person_email.required' => 'Contact email address is required.',
            'contact_person_email.email' => 'Please provide a valid email address.',
            'contact_person_phone.regex' => 'Please provide a valid phone number.',
            'website_url.url' => 'Please provide a valid website URL.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_name' => $this->sanitizeText($this->input('organization_name')),
            'business_description' => $this->sanitizeText($this->input('business_description')),
            'contact_person_name' => $this->sanitizeText($this->input('contact_person_name')),
            'contact_person_email' => $this->sanitizeEmail($this->input('contact_person_email')),
            'contact_person_phone' => $this->sanitizeText($this->input('contact_person_phone')),
            'business_registration_number' => $this->sanitizeText($this->input('business_registration_number')),
            'website_url' => $this->sanitizeUrl($this->input('website_url')),
        ]);
    }

    /**
     * Sanitize text input.
     */
    private function sanitizeText(?string $text): ?string
    {
        if (! $text) {
            return $text;
        }

        // Remove extra whitespace and trim
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Remove any potentially harmful characters
        $text = filter_var($text, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);

        return $text;
    }

    /**
     * Sanitize email input.
     */
    private function sanitizeEmail(?string $email): ?string
    {
        if (! $email) {
            return $email;
        }

        // Trim and convert to lowercase
        $email = strtolower(trim($email));

        // Don't use FILTER_SANITIZE_EMAIL as it can make invalid emails appear valid
        // Just return the trimmed, lowercase email for validation
        return $email;
    }

    /**
     * Sanitize URL input.
     */
    private function sanitizeUrl(?string $url): ?string
    {
        if (! $url) {
            return $url;
        }

        // Trim and sanitize URL
        $url = trim($url);
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return $url;
    }

    /**
     * Get available industry types.
     */
    private function getIndustryTypes(): array
    {
        return [
            'technology' => 'Technology',
            'healthcare' => 'Healthcare',
            'finance' => 'Finance',
            'education' => 'Education',
            'retail' => 'Retail',
            'manufacturing' => 'Manufacturing',
            'consulting' => 'Consulting',
            'nonprofit' => 'Non-Profit',
            'media' => 'Media & Entertainment',
            'real_estate' => 'Real Estate',
            'other' => 'Other',
        ];
    }
}
