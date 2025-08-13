<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProjectRequest;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreProjectRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test valid project creation data passes validation.
     */
    public function test_valid_project_data_passes_validation(): void
    {
        $data = [
            'name' => 'Test Project',
            'description' => 'A test project description',
            'visibility' => ProjectVisibility::PUBLIC->value,
            'requires_approval' => false,
            'max_contributors' => 100,
            'total_amount' => 1000.00,
            'minimum_contribution' => 10.00,
            'payment_options' => ['full', 'installments'],
            'installment_frequency' => 'monthly',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(37)->format('Y-m-d'),
            'registration_deadline' => now()->addDays(5)->format('Y-m-d'),
            'managed_by' => [],
            'settings' => [],
            'products' => [
                [
                    'name' => 'Product 1',
                    'description' => 'First product',
                    'price' => 500.00,
                    'sort_order' => 0,
                ],
                [
                    'name' => 'Product 2',
                    'description' => 'Second product',
                    'price' => 500.00,
                    'sort_order' => 1,
                ],
            ],
        ];

        $request = new StoreProjectRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test required fields validation.
     */
    public function test_required_fields_validation(): void
    {
        $data = [];

        $request = new StoreProjectRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
        $this->assertArrayHasKey('visibility', $validator->errors()->toArray());
        $this->assertArrayHasKey('total_amount', $validator->errors()->toArray());
        $this->assertArrayHasKey('payment_options', $validator->errors()->toArray());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('products', $validator->errors()->toArray());
    }

    /**
     * Test project name validation.
     */
    public function test_project_name_validation(): void
    {
        $request = new StoreProjectRequest();

        // Test invalid characters
        $data = ['name' => 'Project with <script>alert("xss")</script>'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        // Test too long name
        $data = ['name' => str_repeat('a', 256)];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        // Test valid name
        $data = ['name' => 'Valid Project Name & Co.'];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || !$validator->errors()->has('name'));
    }

    /**
     * Test financial validation.
     */
    public function test_financial_validation(): void
    {
        $request = new StoreProjectRequest();

        // Test negative total amount
        $data = ['total_amount' => -100];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('total_amount', $validator->errors()->toArray());

        // Test minimum contribution greater than total
        $data = [
            'total_amount' => 100.00,
            'minimum_contribution' => 200.00,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('minimum_contribution', $validator->errors()->toArray());

        // Test valid amounts
        $data = [
            'total_amount' => 1000.00,
            'minimum_contribution' => 50.00,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('total_amount') && !$validator->errors()->has('minimum_contribution')));
    }

    /**
     * Test date validation.
     */
    public function test_date_validation(): void
    {
        $request = new StoreProjectRequest();

        // Test start date in the past
        $data = [
            'start_date' => now()->subDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());

        // Test end date before start date
        $data = [
            'start_date' => now()->addDays(30)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());

        // Test registration deadline after end date
        $data = [
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'registration_deadline' => now()->addDays(35)->format('Y-m-d'),
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('registration_deadline', $validator->errors()->toArray());
    }

    /**
     * Test payment options validation.
     */
    public function test_payment_options_validation(): void
    {
        $request = new StoreProjectRequest();

        // Test empty payment options
        $data = ['payment_options' => []];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('payment_options', $validator->errors()->toArray());

        // Test invalid payment option
        $data = ['payment_options' => ['invalid_option']];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('payment_options.0', $validator->errors()->toArray());

        // Test installments without frequency (using withValidator)
        $data = [
            'name' => 'Test Project',
            'description' => 'Test description',
            'visibility' => 'public',
            'total_amount' => 1000.00,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(37)->format('Y-m-d'),
            'products' => [['name' => 'Product', 'price' => 1000.00]],
            'payment_options' => ['installments'],
            'installment_frequency' => null,
        ];
        
        $request = StoreProjectRequest::create('/', 'POST', $data);
        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('installment_frequency', $validator->errors()->toArray());

        // Test custom frequency without months (using withValidator)
        $data = [
            'name' => 'Test Project',
            'description' => 'Test description',
            'visibility' => 'public',
            'total_amount' => 1000.00,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(37)->format('Y-m-d'),
            'products' => [['name' => 'Product', 'price' => 1000.00]],
            'payment_options' => ['installments'],
            'installment_frequency' => 'custom',
            'custom_installment_months' => null,
        ];
        
        $request = StoreProjectRequest::create('/', 'POST', $data);
        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('custom_installment_months', $validator->errors()->toArray());
    }

    /**
     * Test product validation.
     */
    public function test_product_validation(): void
    {
        $request = new StoreProjectRequest();

        // Test no products
        $data = ['products' => []];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('products', $validator->errors()->toArray());

        // Test too many products
        $products = [];
        for ($i = 0; $i < 51; $i++) {
            $products[] = [
                'name' => "Product {$i}",
                'price' => 10.00,
            ];
        }
        $data = ['products' => $products];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('products', $validator->errors()->toArray());

        // Test product without name
        $data = [
            'products' => [
                [
                    'description' => 'Product without name',
                    'price' => 100.00,
                ],
            ],
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('products.0.name', $validator->errors()->toArray());

        // Test product without price
        $data = [
            'products' => [
                [
                    'name' => 'Product without price',
                    'description' => 'A product',
                ],
            ],
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('products.0.price', $validator->errors()->toArray());

        // Test negative product price
        $data = [
            'products' => [
                [
                    'name' => 'Negative price product',
                    'price' => -50.00,
                ],
            ],
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('products.0.price', $validator->errors()->toArray());
    }

    /**
     * Test visibility enum validation.
     */
    public function test_visibility_enum_validation(): void
    {
        $request = new StoreProjectRequest();

        // Test invalid visibility
        $data = ['visibility' => 'invalid_visibility'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('visibility', $validator->errors()->toArray());

        // Test valid visibilities
        foreach (ProjectVisibility::cases() as $visibility) {
            $data = ['visibility' => $visibility->value];
            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->passes() || !$validator->errors()->has('visibility'));
        }
    }

    /**
     * Test contributor limits validation.
     */
    public function test_contributor_limits_validation(): void
    {
        $request = new StoreProjectRequest();

        // Test zero max contributors
        $data = ['max_contributors' => 0];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('max_contributors', $validator->errors()->toArray());

        // Test too many max contributors
        $data = ['max_contributors' => 10001];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('max_contributors', $validator->errors()->toArray());

        // Test valid max contributors
        $data = ['max_contributors' => 500];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || !$validator->errors()->has('max_contributors'));
    }
}