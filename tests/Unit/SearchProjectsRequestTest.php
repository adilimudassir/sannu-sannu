<?php

namespace Tests\Unit;

use App\Http\Requests\SearchProjectsRequest;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SearchProjectsRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test valid search data passes validation.
     */
    public function test_valid_search_data_passes_validation(): void
    {
        $data = [
            'search' => 'test project',
            'status' => [ProjectStatus::ACTIVE->value, ProjectStatus::DRAFT->value],
            'visibility' => [ProjectVisibility::PUBLIC->value],
            'min_amount' => 100.00,
            'max_amount' => 1000.00,
            'start_date_from' => '2024-01-01',
            'start_date_to' => '2024-12-31',
            'sort_by' => 'created_at',
            'sort_direction' => 'desc',
            'page' => 1,
            'per_page' => 20,
        ];

        $request = new SearchProjectsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test empty search data passes validation (all filters optional).
     */
    public function test_empty_search_data_passes_validation(): void
    {
        $data = [];

        $request = new SearchProjectsRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test search query validation.
     */
    public function test_search_query_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test too long search query
        $data = ['search' => str_repeat('a', 256)];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('search', $validator->errors()->toArray());

        // Test invalid characters in search
        $data = ['search' => 'search with <script>alert("xss")</script>'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('search', $validator->errors()->toArray());

        // Test valid search query
        $data = ['search' => 'Valid search query & terms'];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || !$validator->errors()->has('search'));
    }

    /**
     * Test status filter validation.
     */
    public function test_status_filter_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test invalid status
        $data = ['status' => ['invalid_status']];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status.0', $validator->errors()->toArray());

        // Test valid statuses
        $data = ['status' => [ProjectStatus::ACTIVE->value, ProjectStatus::DRAFT->value]];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || !$validator->errors()->has('status'));
    }

    /**
     * Test visibility filter validation.
     */
    public function test_visibility_filter_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test invalid visibility
        $data = ['visibility' => ['invalid_visibility']];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('visibility.0', $validator->errors()->toArray());

        // Test valid visibilities
        $data = ['visibility' => [ProjectVisibility::PUBLIC->value, ProjectVisibility::PRIVATE->value]];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || !$validator->errors()->has('visibility'));
    }

    /**
     * Test amount range validation.
     */
    public function test_amount_range_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test negative amounts
        $data = ['min_amount' => -100];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('min_amount', $validator->errors()->toArray());

        // Test max amount less than min amount
        $data = [
            'min_amount' => 1000.00,
            'max_amount' => 500.00,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('max_amount', $validator->errors()->toArray());

        // Test valid amount range
        $data = [
            'min_amount' => 100.00,
            'max_amount' => 1000.00,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('min_amount') && !$validator->errors()->has('max_amount')));
    }

    /**
     * Test date range validation.
     */
    public function test_date_range_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test start_date_to before start_date_from
        $data = [
            'start_date_from' => '2024-12-31',
            'start_date_to' => '2024-01-01',
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('start_date_to', $validator->errors()->toArray());

        // Test end_date_to before end_date_from
        $data = [
            'end_date_from' => '2024-12-31',
            'end_date_to' => '2024-01-01',
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_date_to', $validator->errors()->toArray());

        // Test valid date ranges
        $data = [
            'start_date_from' => '2024-01-01',
            'start_date_to' => '2024-12-31',
            'end_date_from' => '2024-06-01',
            'end_date_to' => '2024-12-31',
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('start_date_to') && !$validator->errors()->has('end_date_to')));
    }

    /**
     * Test contributor range validation.
     */
    public function test_contributor_range_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test negative contributors
        $data = ['min_contributors' => -1];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('min_contributors', $validator->errors()->toArray());

        // Test max contributors less than min contributors
        $data = [
            'min_contributors' => 100,
            'max_contributors' => 50,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('max_contributors', $validator->errors()->toArray());

        // Test valid contributor range
        $data = [
            'min_contributors' => 10,
            'max_contributors' => 100,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('min_contributors') && !$validator->errors()->has('max_contributors')));
    }

    /**
     * Test progress range validation.
     */
    public function test_progress_range_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test progress below 0
        $data = ['min_progress' => -1];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('min_progress', $validator->errors()->toArray());

        // Test progress above 100
        $data = ['max_progress' => 101];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('max_progress', $validator->errors()->toArray());

        // Test max progress less than min progress
        $data = [
            'min_progress' => 80,
            'max_progress' => 50,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('max_progress', $validator->errors()->toArray());

        // Test valid progress range
        $data = [
            'min_progress' => 25,
            'max_progress' => 75,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('min_progress') && !$validator->errors()->has('max_progress')));
    }

    /**
     * Test sorting validation.
     */
    public function test_sorting_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test invalid sort field
        $data = ['sort_by' => 'invalid_field'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('sort_by', $validator->errors()->toArray());

        // Test invalid sort direction
        $data = ['sort_direction' => 'invalid_direction'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('sort_direction', $validator->errors()->toArray());

        // Test valid sorting
        $data = [
            'sort_by' => 'name',
            'sort_direction' => 'asc',
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('sort_by') && !$validator->errors()->has('sort_direction')));
    }

    /**
     * Test pagination validation.
     */
    public function test_pagination_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test invalid page number
        $data = ['page' => 0];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('page', $validator->errors()->toArray());

        // Test invalid per_page (too small)
        $data = ['per_page' => 0];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());

        // Test invalid per_page (too large)
        $data = ['per_page' => 101];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());

        // Test valid pagination
        $data = [
            'page' => 2,
            'per_page' => 25,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('page') && !$validator->errors()->has('per_page')));
    }

    /**
     * Test payment options validation.
     */
    public function test_payment_options_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test invalid payment option
        $data = ['payment_options' => ['invalid_option']];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('payment_options.0', $validator->errors()->toArray());

        // Test valid payment options
        $data = ['payment_options' => ['full', 'installments']];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || !$validator->errors()->has('payment_options'));
    }

    /**
     * Test time-based filter validation.
     */
    public function test_time_based_filter_validation(): void
    {
        $request = new SearchProjectsRequest();

        // Test invalid ending_soon_days
        $data = [
            'ending_soon' => true,
            'ending_soon_days' => 0,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('ending_soon_days', $validator->errors()->toArray());

        // Test ending_soon_days too large
        $data = [
            'ending_soon' => true,
            'ending_soon_days' => 366,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('ending_soon_days', $validator->errors()->toArray());

        // Test valid time-based filters
        $data = [
            'ending_soon' => true,
            'ending_soon_days' => 30,
            'recently_created' => true,
            'recently_created_days' => 7,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes() || (!$validator->errors()->has('ending_soon_days') && !$validator->errors()->has('recently_created_days')));
    }

    /**
     * Test getFilters method returns clean data.
     */
    public function test_get_filters_method(): void
    {
        $data = [
            'search' => 'test',
            'status' => [ProjectStatus::ACTIVE->value],
            'min_amount' => 100.00,
            'max_amount' => null, // Should be filtered out
            'empty_array' => [], // Should be filtered out
            'empty_string' => '', // Should be filtered out
        ];

        $request = SearchProjectsRequest::create('/', 'GET', $data);
        $request->setContainer($this->app);
        $request->validateResolved();

        $filters = $request->getFilters();

        $this->assertArrayHasKey('search', $filters);
        $this->assertArrayHasKey('status', $filters);
        $this->assertArrayHasKey('min_amount', $filters);
        $this->assertArrayNotHasKey('max_amount', $filters);
        $this->assertArrayNotHasKey('empty_array', $filters);
        $this->assertArrayNotHasKey('empty_string', $filters);
    }

    /**
     * Test getPaginationParams method.
     */
    public function test_get_pagination_params_method(): void
    {
        $data = [
            'page' => 3,
            'per_page' => 25,
        ];

        $request = SearchProjectsRequest::create('/', 'GET', $data);
        $request->setContainer($this->app);
        $request->validateResolved();

        $params = $request->getPaginationParams();

        $this->assertEquals(3, $params['page']);
        $this->assertEquals(25, $params['per_page']);
    }

    /**
     * Test getSortParams method.
     */
    public function test_get_sort_params_method(): void
    {
        $data = [
            'sort_by' => 'name',
            'sort_direction' => 'asc',
        ];

        $request = SearchProjectsRequest::create('/', 'GET', $data);
        $request->setContainer($this->app);
        $request->validateResolved();

        $params = $request->getSortParams();

        $this->assertEquals('name', $params['sort_by']);
        $this->assertEquals('asc', $params['sort_direction']);
    }

    /**
     * Test default values are set correctly.
     */
    public function test_default_values_are_set(): void
    {
        $request = SearchProjectsRequest::create('/', 'GET', []);
        $request->setContainer($this->app);
        $request->validateResolved();

        $sortParams = $request->getSortParams();
        $paginationParams = $request->getPaginationParams();

        $this->assertEquals('created_at', $sortParams['sort_by']);
        $this->assertEquals('desc', $sortParams['sort_direction']);
        $this->assertEquals(1, $paginationParams['page']);
        $this->assertEquals(15, $paginationParams['per_page']);
    }
}