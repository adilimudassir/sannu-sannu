<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
            // Basic project information
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\'\.&()]+$/', // Allow alphanumeric, spaces, and common punctuation
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
            ],

            // Project visibility and access
            'visibility' => [
                'required',
                Rule::enum(ProjectVisibility::class),
            ],
            'requires_approval' => [
                'boolean',
            ],
            'max_contributors' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],

            // Financial details
            'total_amount' => [
                'required',
                'numeric',
                'min:1',
                'decimal:0,2',
            ],
            'minimum_contribution' => [
                'nullable',
                'numeric',
                'min:1',
                'decimal:0,2',
                'lte:total_amount',
            ],

            // Payment flexibility
            'payment_options' => [
                'required',
                'array',
                'min:1',
            ],
            'payment_options.*' => [
                'string',
                Rule::in(['full', 'installments']),
            ],
            'installment_frequency' => [
                'nullable',
                Rule::in(['monthly', 'quarterly', 'custom']),
            ],
            'custom_installment_months' => [
                'nullable',
                'integer',
                'min:2',
                'max:60',
            ],

            // Timeline
            'start_date' => [
                'required',
                'date',
                'after:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
            'registration_deadline' => [
                'nullable',
                'date',
                'after:today',
                'before:end_date',
            ],

            // Project management
            'managed_by' => [
                'nullable',
                'array',
            ],
            'managed_by.*' => [
                'integer',
                'exists:users,id',
            ],

            // Settings
            'settings' => [
                'nullable',
                'array',
            ],

            // Products (required for project creation)
            'products' => [
                'required',
                'array',
                'min:1',
                'max:50',
            ],
            'products.*.name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\'\.&()]+$/',
            ],
            'products.*.description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'products.*.price' => [
                'required',
                'numeric',
                'min:1.00',
                'decimal:0,2',
            ],
            'products.*.image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048', // 2MB max
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
            'products.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            // System admin specific fields
            'tenant_id' => [
                $this->isAdminRoute() ? 'required' : 'nullable',
                'integer',
                'exists:tenants,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The project name may only contain letters, numbers, spaces, and common punctuation.',
            'name.required' => 'A project name is required.',
            'description.required' => 'A project description is required.',
            'description.max' => 'The project description may not be longer than 5000 characters.',

            'visibility.required' => 'Please select a project visibility option.',
            'max_contributors.min' => 'Maximum contributors must be at least 1.',
            'max_contributors.max' => 'Maximum contributors cannot exceed 10,000.',

            'total_amount.required' => 'A total contribution amount is required.',
            'total_amount.min' => 'The total amount must be at least ₦1.00',
            'minimum_contribution.lte' => 'The minimum contribution cannot be greater than the total amount.',

            'payment_options.required' => 'Please select at least one payment option.',
            'payment_options.min' => 'At least one payment option must be selected.',
            'installment_frequency.required_if' => 'Installment frequency is required when installments are allowed.',
            'custom_installment_months.required_if' => 'Custom installment months is required when using custom frequency.',
            'custom_installment_months.min' => 'Custom installment period must be at least 2 months.',
            'custom_installment_months.max' => 'Custom installment period cannot exceed 60 months.',

            'start_date.required' => 'A project start date is required.',
            'start_date.after' => 'The start date must be in the future.',
            'end_date.required' => 'A project end date is required.',
            'end_date.after' => 'The end date must be after the start date.',
            'registration_deadline.before' => 'The registration deadline must be before the project end date.',

            'managed_by.*.exists' => 'One or more selected managers do not exist.',

            'products.required' => 'At least one product is required for the project.',
            'products.min' => 'At least one product must be added to the project.',
            'products.max' => 'A project cannot have more than 50 products.',
            'products.*.name.required' => 'Each product must have a name.',
            'products.*.name.regex' => 'Product names may only contain letters, numbers, spaces, and common punctuation.',
            'products.*.price.required' => 'Each product must have a price.',
            'products.*.price.min' => 'Product prices must be at least₦1.00',
            'products.*.image.image' => 'Product images must be valid image files.',
            'products.*.image.mimes' => 'Product images must be JPEG, JPG, PNG, or WebP format.',
            'products.*.image.max' => 'Product images cannot be larger than 2MB.',
            'products.*.image.dimensions' => 'Product images must be between 100x100 and 2000x2000 pixels.',

            'tenant_id.exists' => 'The selected tenant does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'project name',
            'description' => 'project description',
            'visibility' => 'project visibility',
            'requires_approval' => 'approval requirement',
            'max_contributors' => 'maximum contributors',
            'total_amount' => 'total contribution amount',
            'minimum_contribution' => 'minimum contribution',
            'payment_options' => 'payment options',
            'installment_frequency' => 'installment frequency',
            'custom_installment_months' => 'custom installment months',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'registration_deadline' => 'registration deadline',
            'managed_by' => 'project managers',
            'products' => 'products',
            'tenant_id' => 'tenant',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->sanitizeString($this->name),
            'description' => $this->sanitizeString($this->description),
            'requires_approval' => $this->boolean('requires_approval'),
            'payment_options' => $this->ensureArray('payment_options'),
            'managed_by' => $this->ensureArray('managed_by'),
            'settings' => $this->ensureArray('settings'),
        ]);

        // Sanitize product data
        if ($this->has('products') && is_array($this->products)) {
            $products = [];
            foreach ($this->products as $index => $product) {
                $products[$index] = [
                    'name' => $this->sanitizeString($product['name'] ?? null),
                    'description' => $this->sanitizeString($product['description'] ?? null),
                    'price' => $product['price'] ?? null,
                    'image' => $product['image'] ?? null,
                    'sort_order' => $product['sort_order'] ?? $index,
                ];
            }
            $this->merge(['products' => $products]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that product prices sum to total amount
            if ($this->has('products') && $this->has('total_amount')) {
                $productTotal = collect($this->products)->sum('price');
                $totalAmount = (float) $this->total_amount;

                if (abs($productTotal - $totalAmount) > 0.01) {
                    $validator->errors()->add(
                        'total_amount',
                        'The total amount must equal the sum of all product prices.'
                    );
                }
            }

            // Validate installment options consistency
            if (in_array('installments', $this->payment_options ?? [])) {
                if (empty($this->installment_frequency)) {
                    $validator->errors()->add(
                        'installment_frequency',
                        'Installment frequency is required when installments are enabled.'
                    );
                }

                if ($this->installment_frequency === 'custom' && empty($this->custom_installment_months)) {
                    $validator->errors()->add(
                        'custom_installment_months',
                        'Custom installment months is required when using custom frequency.'
                    );
                }
            }

            // Validate registration deadline is reasonable
            if ($this->registration_deadline && $this->start_date) {
                $registrationDate = \Carbon\Carbon::parse($this->registration_deadline);
                $startDate = \Carbon\Carbon::parse($this->start_date);

                if ($registrationDate->diffInDays($startDate) < 1) {
                    $validator->errors()->add(
                        'registration_deadline',
                        'Registration deadline should be at least 1 day before the start date.'
                    );
                }
            }
        });
    }

    /**
     * Sanitize string input.
     */
    private function sanitizeString(?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        // Remove extra whitespace and trim
        $value = preg_replace('/\s+/', ' ', trim($value));

        // Remove potentially harmful characters
        $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);

        return $value;
    }

    /**
     * Ensure value is an array.
     */
    private function ensureArray($key): array
    {
        $value = $this->input($key);

        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            return array_filter(explode(',', $value));
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Get project data for service layer
     */
    public function getProjectData(): array
    {
        return $this->only([
            'name',
            'description',
            'visibility',
            'requires_approval',
            'max_contributors',
            'total_amount',
            'minimum_contribution',
            'payment_options',
            'installment_frequency',
            'custom_installment_months',
            'start_date',
            'end_date',
            'registration_deadline',
            'managed_by',
            'settings',
        ]);
    }

    /**
     * Get products data for service layer
     */
    public function getProductsData(): array
    {
        return $this->input('products', []);
    }

    /**
     * Get the tenant from the request
     */
    public function tenant()
    {
        return app('tenant');
    }

    /**
     * Check if this is an admin route
     */
    private function isAdminRoute(): bool
    {
        return $this->route() && str_starts_with($this->route()->getName() ?? '', 'admin.');
    }
}
