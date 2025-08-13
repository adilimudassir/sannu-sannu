<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ProductService;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;
    private Tenant $tenant;
    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productService = app(ProductService::class);
        
        // Create test data
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'total_amount' => 0.00,
        ]);
    }

    public function test_complete_product_lifecycle()
    {
        Storage::fake('public');

        // 1. Add product with image
        $image = UploadedFile::fake()->image('product.jpg', 800, 600);
        
        $productData = [
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 150.00,
            'image' => $image,
        ];

        $product = $this->productService->addProduct($this->project, $productData);

        // Verify product was created
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Test Product',
            'price' => 150.00,
            'project_id' => $this->project->id,
        ]);

        // Verify image was uploaded
        $this->assertNotNull($product->image_url);
        Storage::disk('public')->assertExists($product->image_url);

        // Verify project total was updated
        $this->project->refresh();
        $this->assertEquals(150.00, $this->project->total_amount);

        // 2. Add second product
        $product2 = $this->productService->addProduct($this->project, [
            'name' => 'Second Product',
            'price' => 75.00,
        ]);

        // Verify project total includes both products
        $this->project->refresh();
        $this->assertEquals(225.00, $this->project->total_amount);

        // 3. Reorder products
        $this->productService->reorderProducts($this->project, [
            $product2->id,
            $product->id,
        ]);

        // Verify order changed
        $product->refresh();
        $product2->refresh();
        $this->assertEquals(2, $product->sort_order);
        $this->assertEquals(1, $product2->sort_order);

        // 4. Update product
        $newImage = UploadedFile::fake()->image('updated.jpg');
        $oldImageUrl = $product->image_url;
        
        $updatedProduct = $this->productService->updateProduct($product, [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 200.00,
            'image' => $newImage,
        ]);

        // Verify update
        $this->assertEquals('Updated Product', $updatedProduct->name);
        $this->assertEquals(200.00, $updatedProduct->price);
        $this->assertNotEquals($oldImageUrl, $updatedProduct->image_url);

        // Verify old image was deleted
        Storage::disk('public')->assertMissing($oldImageUrl);
        Storage::disk('public')->assertExists($updatedProduct->image_url);

        // Verify project total updated
        $this->project->refresh();
        $this->assertEquals(275.00, $this->project->total_amount);

        // 5. Delete product
        $imageToDelete = $updatedProduct->image_url;
        
        $result = $this->productService->deleteProduct($updatedProduct);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('products', ['id' => $updatedProduct->id]);
        Storage::disk('public')->assertMissing($imageToDelete);

        // Verify project total updated
        $this->project->refresh();
        $this->assertEquals(75.00, $this->project->total_amount);
    }

    public function test_get_project_products_with_multiple_projects()
    {
        // Create another project
        $otherProject = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
        ]);

        // Add products to both projects
        $this->productService->addProduct($this->project, [
            'name' => 'Project 1 Product',
            'price' => 100.00,
        ]);

        $this->productService->addProduct($otherProject, [
            'name' => 'Project 2 Product',
            'price' => 200.00,
        ]);

        // Get products for each project
        $project1Products = $this->productService->getProjectProducts($this->project);
        $project2Products = $this->productService->getProjectProducts($otherProject);

        $this->assertCount(1, $project1Products);
        $this->assertCount(1, $project2Products);
        $this->assertEquals('Project 1 Product', $project1Products->first()->name);
        $this->assertEquals('Project 2 Product', $project2Products->first()->name);
    }

    public function test_cleanup_unused_images_integration()
    {
        Storage::fake('public');

        // Create product with image
        $image = UploadedFile::fake()->image('test.jpg');
        $product = $this->productService->addProduct($this->project, [
            'name' => 'Test Product',
            'price' => 100.00,
            'image' => $image,
        ]);

        // Create orphaned image file
        $orphanedImage = UploadedFile::fake()->image('orphaned.jpg');
        $orphanedPath = $orphanedImage->storeAs('products', 'orphaned.jpg', 'public');

        // Verify both images exist
        Storage::disk('public')->assertExists($product->image_url);
        Storage::disk('public')->assertExists($orphanedPath);

        // Run cleanup
        $cleanedCount = $this->productService->cleanupUnusedImages();

        // Verify only orphaned image was deleted
        $this->assertEquals(1, $cleanedCount);
        Storage::disk('public')->assertExists($product->image_url);
        Storage::disk('public')->assertMissing($orphanedPath);
    }
}