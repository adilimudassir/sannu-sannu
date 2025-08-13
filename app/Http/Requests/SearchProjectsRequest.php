<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchProjectsRequest extends FormRequest
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
            // Search query
            'search' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\'\.&()]+$/',
            ],
            
            // Status filters
            'status' => [
                'nullable',
                'array',
            ],
            'status.*' => [
                Rule::enum(ProjectStatus::class),
            ],
            
            // Visibility filters
            'visibility' => [
                'nullable',
                'array',
            ],
            'visibility.*' => [
                Rule::enum(ProjectVisibility::class),
            ],
            
            // Tenant filter (for system admin)
            'tenant_id' => [
                'nullable',
                'integer',
                'exists:tenants,id',
            ],
            'tenant_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            
            // Amount filters
            'min_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                'decimal:0,2',
            ],
            'max_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                'decimal:0,2',
                'gte:min_amount',
            ],
            
            // Date filters
            'start_date_from' => [
                'nullable',
                'date',
            ],
            'start_date_to' => [
                'nullable',
                'date',
                'after_or_equal:start_date_from',
            ],
            'end_date_from' => [
                'nullable',
                'date',
            ],
            'end_date_to' => [
                'nullable',
                'date',
                'after_or_equal:end_date_from',
            ],
            'created_from' => [
                'nullable',
                'date',
            ],
            'created_to' => [
                'nullable',
                'date',
                'after_or_equal:created_from',
            ],
            
            // Contribution filters
            'min_contributors' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'max_contributors' => [
                'nullable',
                'integer',
                'min:0',
                'gte:min_contributors',
            ],
            'has_contributions' => [
                'nullable',
                'boolean',
            ],
            
            // Progress filters
            'min_progress' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'max_progress' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
                'gte:min_progress',
            ],
            
            // Sorting
            'sort_by' => [
                'nullable',
                'string',
                Rule::in([
                    'name',
                    'created_at',
                    'start_date',
                    'end_date',
                    'total_amount',
                    'status',
                    'contributor_count',
                    'progress',
                    'popularity',
                ]),
            ],
            'sort_direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
            
            // Pagination
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            
            // Additional filters
            'requires_approval' => [
                'nullable',
                'boolean',
            ],
            'accepting_contributions' => [
                'nullable',
                'boolean',
            ],
            'has_products' => [
                'nullable',
                'boolean',
            ],
            'payment_options' => [
                'nullable',
                'array',
            ],
            'payment_options.*' => [
                'string',
                Rule::in(['full', 'installments']),
            ],
            
            // Creator filter
            'created_by' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'creator_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            
            // Time-based filters
            'ending_soon' => [
                'nullable',
                'boolean',
            ],
            'ending_soon_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:365',
            ],
            'recently_created' => [
                'nullable',
                'boolean',
            ],
            'recently_created_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:365',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'search.regex' => 'Search query contains invalid characters.',
            'search.max' => 'Search query cannot be longer than 255 characters.',
            
            'tenant_id.exists' => 'The selected tenant does not exist.',
            'tenant_name.max' => 'Tenant name filter cannot be longer than 255 characters.',
            
            'min_amount.min' => 'Minimum amount must be at least $0.',
            'min_amount.max' => 'Minimum amount cannot exceed $999,999.99.',
            'max_amount.min' => 'Maximum amount must be at least $0.',
            'max_amount.max' => 'Maximum amount cannot exceed $999,999.99.',
            'max_amount.gte' => 'Maximum amount must be greater than or equal to minimum amount.',
            
            'start_date_to.after_or_equal' => 'Start date "to" must be after or equal to start date "from".',
            'end_date_to.after_or_equal' => 'End date "to" must be after or equal to end date "from".',
            'created_to.after_or_equal' => 'Created date "to" must be after or equal to created date "from".',
            
            'max_contributors.gte' => 'Maximum contributors must be greater than or equal to minimum contributors.',
            
            'min_progress.min' => 'Minimum progress must be at least 0%.',
            'min_progress.max' => 'Minimum progress cannot exceed 100%.',
            'max_progress.min' => 'Maximum progress must be at least 0%.',
            'max_progress.max' => 'Maximum progress cannot exceed 100%.',
            'max_progress.gte' => 'Maximum progress must be greater than or equal to minimum progress.',
            
            'sort_by.in' => 'Invalid sort field selected.',
            'sort_direction.in' => 'Sort direction must be either "asc" or "desc".',
            
            'page.min' => 'Page number must be at least 1.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            
            'created_by.exists' => 'The selected creator does not exist.',
            'creator_name.max' => 'Creator name filter cannot be longer than 255 characters.',
            
            'ending_soon_days.min' => 'Ending soon days must be at least 1.',
            'ending_soon_days.max' => 'Ending soon days cannot exceed 365.',
            'recently_created_days.min' => 'Recently created days must be at least 1.',
            'recently_created_days.max' => 'Recently created days cannot exceed 365.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'search query',
            'status' => 'project status',
            'visibility' => 'project visibility',
            'tenant_id' => 'tenant',
            'tenant_name' => 'tenant name',
            'min_amount' => 'minimum amount',
            'max_amount' => 'maximum amount',
            'start_date_from' => 'start date from',
            'start_date_to' => 'start date to',
            'end_date_from' => 'end date from',
            'end_date_to' => 'end date to',
            'created_from' => 'created date from',
            'created_to' => 'created date to',
            'min_contributors' => 'minimum contributors',
            'max_contributors' => 'maximum contributors',
            'has_contributions' => 'has contributions',
            'min_progress' => 'minimum progress',
            'max_progress' => 'maximum progress',
            'sort_by' => 'sort field',
            'sort_direction' => 'sort direction',
            'page' => 'page number',
            'per_page' => 'items per page',
            'requires_approval' => 'requires approval',
            'accepting_contributions' => 'accepting contributions',
            'has_products' => 'has products',
            'payment_options' => 'payment options',
            'created_by' => 'creator',
            'creator_name' => 'creator name',
            'ending_soon' => 'ending soon',
            'ending_soon_days' => 'ending soon days',
            'recently_created' => 'recently created',
            'recently_created_days' => 'recently created days',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];
        
        // Sanitize search query
        if ($this->has('search')) {
            $data['search'] = $this->sanitizeString($this->search);
        }
        
        // Sanitize tenant name
        if ($this->has('tenant_name')) {
            $data['tenant_name'] = $this->sanitizeString($this->tenant_name);
        }
        
        // Sanitize creator name
        if ($this->has('creator_name')) {
            $data['creator_name'] = $this->sanitizeString($this->creator_name);
        }
        
        // Ensure arrays for multi-select filters
        if ($this->has('status')) {
            $data['status'] = $this->ensureArray('status');
        }
        
        if ($this->has('visibility')) {
            $data['visibility'] = $this->ensureArray('visibility');
        }
        
        if ($this->has('payment_options')) {
            $data['payment_options'] = $this->ensureArray('payment_options');
        }
        
        // Convert boolean strings to actual booleans
        $booleanFields = [
            'has_contributions',
            'requires_approval',
            'accepting_contributions',
            'has_products',
            'ending_soon',
            'recently_created',
        ];
        
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $data[$field] = $this->boolean($field);
            }
        }
        
        // Set default values
        if (!$this->has('sort_by')) {
            $data['sort_by'] = 'created_at';
        }
        
        if (!$this->has('sort_direction')) {
            $data['sort_direction'] = 'desc';
        }
        
        if (!$this->has('per_page')) {
            $data['per_page'] = 15;
        }
        
        if (!$this->has('ending_soon_days') && $this->boolean('ending_soon')) {
            $data['ending_soon_days'] = 30;
        }
        
        if (!$this->has('recently_created_days') && $this->boolean('recently_created')) {
            $data['recently_created_days'] = 7;
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
            // Validate date range consistency
            if ($this->start_date_from && $this->end_date_to) {
                $startFrom = \Carbon\Carbon::parse($this->start_date_from);
                $endTo = \Carbon\Carbon::parse($this->end_date_to);
                
                if ($startFrom->gt($endTo)) {
                    $validator->errors()->add('end_date_to', 
                        'Project end date must be after project start date.');
                }
            }
            
            // Validate logical filter combinations
            if ($this->accepting_contributions && $this->has('status')) {
                $acceptingStatuses = [ProjectStatus::ACTIVE->value];
                $hasAcceptingStatus = !empty(array_intersect($this->status, $acceptingStatuses));
                
                if (!$hasAcceptingStatus) {
                    $validator->errors()->add('accepting_contributions', 
                        'Projects accepting contributions must have "active" status.');
                }
            }
            
            // Validate progress filters make sense with contribution filters
            if (($this->min_progress > 0 || $this->max_progress !== null) && $this->has_contributions === false) {
                $validator->errors()->add('min_progress', 
                    'Progress filters cannot be used when filtering for projects without contributions.');
            }
        });
    }

    /**
     * Get the validated and processed filters.
     */
    public function getFilters(): array
    {
        $validated = $this->validated();
        
        // Remove null values and empty arrays
        return array_filter($validated, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });
    }

    /**
     * Get pagination parameters.
     */
    public function getPaginationParams(): array
    {
        return [
            'page' => $this->validated('page', 1),
            'per_page' => $this->validated('per_page', 15),
        ];
    }

    /**
     * Get sorting parameters.
     */
    public function getSortParams(): array
    {
        return [
            'sort_by' => $this->validated('sort_by', 'created_at'),
            'sort_direction' => $this->validated('sort_direction', 'desc'),
        ];
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
     * Get the tenant from the request
     */
    public function tenant()
    {
        return app('tenant');
    }
}