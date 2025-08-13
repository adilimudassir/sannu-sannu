<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
        $project = $this->route('project');
        $hasActiveContributions = $project && $project->contributions()->exists();
        
        return [
            // Basic project information
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\'\.&()]+$/',
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'max:5000',
            ],
            
            // Project visibility and access
            'visibility' => [
                'sometimes',
                'required',
                Rule::enum(ProjectVisibility::class),
            ],
            'requires_approval' => [
                'sometimes',
                'boolean',
            ],
            'max_contributors' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            
            // Financial details - restricted if project has active contributions
            'total_amount' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
                $hasActiveContributions ? 'prohibited' : '',
            ],
            'minimum_contribution' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
                'lte:total_amount',
            ],
            
            // Payment flexibility - restricted if project has active contributions
            'payment_options' => [
                'sometimes',
                'required',
                'array',
                'min:1',
                $hasActiveContributions ? 'prohibited' : '',
            ],
            'payment_options.*' => [
                'string',
                Rule::in(['full', 'installments']),
            ],
            'installment_frequency' => [
                'sometimes',
                'nullable',
                Rule::in(['monthly', 'quarterly', 'custom']),
                $hasActiveContributions ? 'prohibited' : '',
            ],
            'custom_installment_months' => [
                'sometimes',
                'nullable',
                'integer',
                'min:2',
                'max:60',
                $hasActiveContributions ? 'prohibited' : '',
            ],
            
            // Timeline - some restrictions if project has active contributions
            'start_date' => [
                'sometimes',
                'required',
                'date',
                $project && $project->status === ProjectStatus::ACTIVE ? 'prohibited' : 'after:today',
            ],
            'end_date' => [
                'sometimes',
                'required',
                'date',
                'after:start_date',
            ],
            'registration_deadline' => [
                'sometimes',
                'nullable',
                'date',
                'after:today',
                'before:end_date',
            ],
            
            // Project management
            'managed_by' => [
                'sometimes',
                'nullable',
                'array',
            ],
            'managed_by.*' => [
                'integer',
                'exists:users,id',
            ],
            
            // Status updates
            'status' => [
                'sometimes',
                'required',
                Rule::enum(ProjectStatus::class),
            ],
            
            // Settings
            'settings' => [
                'sometimes',
                'nullable',
                'array',
            ],
            
            // Products - can be updated but with restrictions
            'products' => [
                'sometimes',
                'required',
                'array',
                'min:1',
                'max:50',
            ],
            'products.*.id' => [
                'sometimes',
                'integer',
                'exists:products,id',
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
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
                // Prevent price changes if project has contributions
                function ($attribute, $value, $fail) use ($project) {
                    if (!$project) return;
                    
                    $productIndex = explode('.', $attribute)[1];
                    $productId = $this->input("products.{$productIndex}.id");
                    
                    if ($productId && $project->contributions()->exists()) {
                        $product = $project->products()->find($productId);
                        if ($product && $product->price != $value) {
                            $fail('Cannot change price of a product when the project has contributions.');
                        }
                    }
                },
            ],
            'products.*.image' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
            'products.*.sort_order' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
            'products.*.delete' => [
                'sometimes',
                'boolean',
            ],
            
            // System admin specific fields
            'tenant_id' => [
                'sometimes',
                'integer',
                'exists:tenants,id',
                $hasActiveContributions ? 'prohibited' : '',
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
            'description.max' => 'The project description may not be longer than 5000 characters.',
            
            'max_contributors.min' => 'Maximum contributors must be at least 1.',
            'max_contributors.max' => 'Maximum contributors cannot exceed 10,000.',
            
            'total_amount.prohibited' => 'Cannot change total amount for projects with active contributions.',
            'total_amount.min' => 'The total amount must be at least $0.01.',
            'total_amount.max' => 'The total amount cannot exceed $999,999.99.',
            'minimum_contribution.lte' => 'The minimum contribution cannot be greater than the total amount.',
            
            'payment_options.prohibited' => 'Cannot change payment options for projects with active contributions.',
            'payment_options.min' => 'At least one payment option must be selected.',
            'installment_frequency.prohibited' => 'Cannot change installment frequency for projects with active contributions.',
            'installment_frequency.required_if' => 'Installment frequency is required when installments are allowed.',
            'custom_installment_months.prohibited' => 'Cannot change custom installment months for projects with active contributions.',
            'custom_installment_months.required_if' => 'Custom installment months is required when using custom frequency.',
            'custom_installment_months.min' => 'Custom installment period must be at least 2 months.',
            'custom_installment_months.max' => 'Custom installment period cannot exceed 60 months.',
            
            'start_date.prohibited' => 'Cannot change start date for active projects.',
            'start_date.after' => 'The start date must be in the future.',
            'end_date.after' => 'The end date must be after the start date.',
            'registration_deadline.before' => 'The registration deadline must be before the project end date.',
            
            'managed_by.*.exists' => 'One or more selected managers do not exist.',
            
            'products.min' => 'At least one product must remain in the project.',
            'products.max' => 'A project cannot have more than 50 products.',
            'products.*.name.required' => 'Each product must have a name.',
            'products.*.name.regex' => 'Product names may only contain letters, numbers, spaces, and common punctuation.',
            'products.*.price.required' => 'Each product must have a price.',
            'products.*.price.min' => 'Product prices must be at least $0.01.',
            'products.*.price.max' => 'Product prices cannot exceed $999,999.99.',
            'products.*.image.image' => 'Product images must be valid image files.',
            'products.*.image.mimes' => 'Product images must be JPEG, JPG, PNG, or WebP format.',
            'products.*.image.max' => 'Product images cannot be larger than 2MB.',
            'products.*.image.dimensions' => 'Product images must be between 100x100 and 2000x2000 pixels.',
            
            'tenant_id.prohibited' => 'Cannot change tenant for projects with active contributions.',
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
            'status' => 'project status',
            'products' => 'products',
            'tenant_id' => 'tenant',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];
        
        if ($this->has('name')) {
            $data['name'] = $this->sanitizeString($this->name);
        }
        
        if ($this->has('description')) {
            $data['description'] = $this->sanitizeString($this->description);
        }
        
        if ($this->has('requires_approval')) {
            $data['requires_approval'] = $this->boolean('requires_approval');
        }
        
        if ($this->has('payment_options')) {
            $data['payment_options'] = $this->ensureArray('payment_options');
        }
        
        if ($this->has('managed_by')) {
            $data['managed_by'] = $this->ensureArray('managed_by');
        }
        
        if ($this->has('settings')) {
            $data['settings'] = $this->ensureArray('settings');
        }

        // Sanitize product data if present
        if ($this->has('products') && is_array($this->products)) {
            $products = [];
            foreach ($this->products as $index => $product) {
                $products[$index] = [
                    'id' => $product['id'] ?? null,
                    'name' => $this->sanitizeString($product['name'] ?? null),
                    'description' => $this->sanitizeString($product['description'] ?? null),
                    'price' => $product['price'] ?? null,
                    'image' => $product['image'] ?? null,
                    'sort_order' => $product['sort_order'] ?? $index,
                    'delete' => $product['delete'] ?? false,
                ];
            }
            $data['products'] = $products;
        }
        
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $project = $this->route('project');
            
            // Validate that product prices sum to total amount if both are being updated
            if ($this->has('products') && $this->has('total_amount')) {
                $productTotal = collect($this->products)
                    ->where('delete', '!=', true)
                    ->sum('price');
                $totalAmount = (float) $this->total_amount;
                
                if (abs($productTotal - $totalAmount) > 0.01) {
                    $validator->errors()->add('total_amount', 
                        'The total amount must equal the sum of all product prices.');
                }
            }

            // Validate installment options consistency
            if ($this->has('payment_options') && in_array('installments', $this->payment_options ?? [])) {
                if ($this->has('installment_frequency') && empty($this->installment_frequency)) {
                    $validator->errors()->add('installment_frequency', 
                        'Installment frequency is required when installments are enabled.');
                }
                
                if ($this->installment_frequency === 'custom' && $this->has('custom_installment_months') && empty($this->custom_installment_months)) {
                    $validator->errors()->add('custom_installment_months', 
                        'Custom installment months is required when using custom frequency.');
                }
            }

            // Validate registration deadline is reasonable
            if ($this->has('registration_deadline') && $this->has('start_date') && $this->registration_deadline && $this->start_date) {
                $registrationDate = \Carbon\Carbon::parse($this->registration_deadline);
                $startDate = \Carbon\Carbon::parse($this->start_date);
                
                if ($registrationDate->diffInDays($startDate) < 1) {
                    $validator->errors()->add('registration_deadline', 
                        'Registration deadline should be at least 1 day before the start date.');
                }
            }

            // Validate status transitions
            if ($this->has('status') && $project) {
                $currentStatus = $project->status;
                $newStatus = ProjectStatus::from($this->status);
                
                // Define valid status transitions
                $validTransitions = [
                    ProjectStatus::DRAFT->value => [ProjectStatus::ACTIVE->value, ProjectStatus::CANCELLED->value],
                    ProjectStatus::ACTIVE->value => [ProjectStatus::PAUSED->value, ProjectStatus::COMPLETED->value, ProjectStatus::CANCELLED->value],
                    ProjectStatus::PAUSED->value => [ProjectStatus::ACTIVE->value, ProjectStatus::CANCELLED->value],
                    ProjectStatus::COMPLETED->value => [], // Final state
                    ProjectStatus::CANCELLED->value => [], // Final state
                ];
                
                if (!in_array($newStatus->value, $validTransitions[$currentStatus->value] ?? [])) {
                    $validator->errors()->add('status', 
                        "Cannot change status from {$currentStatus->label()} to {$newStatus->label()}.");
                }
            }

            // Validate product deletions don't leave project empty
            if ($this->has('products') && $project) {
                $remainingProducts = collect($this->products)->where('delete', '!=', true);
                if ($remainingProducts->isEmpty()) {
                    $validator->errors()->add('products', 
                        'Cannot delete all products. At least one product must remain.');
                }
                
                // Check if products being deleted when project has contributions
                foreach ($this->products as $index => $productData) {
                    if (($productData['delete'] ?? false) && isset($productData['id'])) {
                        if ($project->contributions()->exists()) {
                            $validator->errors()->add("products.{$index}.delete", 
                                'Cannot delete products when the project has contributions.');
                        }
                    }
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
            'status',
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
     * Check if request has products data
     */
    public function hasProductsData(): bool
    {
        return $this->has('products') && is_array($this->input('products'));
    }

    /**
     * Get the tenant from the request
     */
    public function tenant()
    {
        return app('tenant');
    }
}
