<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ProductService;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Contribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;
    private Tenant $tenant;
    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productService = new ProductService();
        
        // Create test data
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->user->id,
            'total_amount' => 0.00,
        ]);
    }

    public function test_add_product_creates_product_successfully()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.50,
        ];

        $product = $this->productService->addProduct($this->project, $productData);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals(100.50, $product->price);
        $this->assertEquals($this->project->id, $product->project_id);
        $this->assertEquals($this->tenant->id, $product->tenant_id);
        $this->assertEquals(1, $product->sort_order);
    }

    public function test_add_product_with_image_uploads_file()
    {
        Storage::fake('public');
        
        $image = UploadedFile::fake()->image('product.jpg', 800, 600);
        
        $productData = [
            'name' => 'Test Product',
            'price' => 100.00,
            'image' => $image,
        ];

        $product = $this->productService->addProduct($this->project, $productData);

        $this->assertNotNull($product->image_url);
        $this->assertStringStartsWith('products/', $product->image_url);
        Storage::disk('public')->assertExists($product->image_url);
    }

    public function test_add_product_updates_project_total()
    {
        $productData = [
            'name' => 'Test Product',
            'price' => 100.00,
        ];

        $this->productService->addProduct($this->project, $productData);

        $this->project->refresh();
        $this->assertEquals(100.00, $this->project->total_amount);
    }

    public function test_add_product_sets_correct_sort_order()
    {
        // Create first product
        $product1 = $this->productService->addProduct($this->project, [
            'name' => 'Product 1',
            'price' => 50.00,
        ]);

        // Create second product
        $product2 = $this->productService->addProduct($this->project, [
            'name' => 'Product 2',
            'price' => 75.00,
        ]);

        $this->assertEquals(1, $product1->sort_order);
        $this->assertEquals(2, $product2->sort_order);
    }

    public function test_add_product_validates_required_fields()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name is required.');

        $this->productService->addProduct($this->project, [
            'price' => 100.00,
        ]);
    }

    public function test_add_product_validates_price()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product price must be a valid positive number.');

        $this->productService->addProduct($this->project, [
            'name' => 'Test Product',
            'price' => -10.00,
        ]);
    }

    public function test_update_product_modifies_existing_product()
    {
        $product = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'Original Name',
            'price' => 50.00,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'price' => 75.00,
        ];

        $updatedProduct = $this->productService->updateProduct($product, $updateData);

        $this->assertEquals('Updated Name', $updatedProduct->name);
        $this->assertEquals('Updated Description', $updatedProduct->description);
        $this->assertEquals(75.00, $updatedProduct->price);
    }

    public function test_update_product_with_new_image_replaces_old_image()
    {
        Storage::fake('public');
        
        // Create product with existing image
        $oldImage = UploadedFile::fake()->image('old.jpg');
        $oldImagePath = $oldImage->storeAs('products', 'old.jpg', 'public');
        
        $product = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'image_url' => $oldImagePath,
        ]);

        // Update with new image
        $newImage = UploadedFile::fake()->image('new.jpg');
        
        $updateData = [
            'name' => $product->name,
            'price' => $product->price,
            'image' => $newImage,
        ];

        $updatedProduct = $this->productService->updateProduct($product, $updateData);

        $this->assertNotEquals($oldImagePath, $updatedProduct->image_url);
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($updatedProduct->image_url);
    }

    public function test_update_product_can_remove_image()
    {
        Storage::fake('public');
        
        $image = UploadedFile::fake()->image('test.jpg');
        $imagePath = $image->storeAs('products', 'test.jpg', 'public');
        
        $product = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'image_url' => $imagePath,
        ]);

        $updateData = [
            'name' => $product->name,
            'price' => $product->price,
            'remove_image' => true,
        ];

        $updatedProduct = $this->productService->updateProduct($product, $updateData);

        $this->assertNull($updatedProduct->image_url);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_delete_product_removes_product_and_image()
    {
        Storage::fake('public');
        
        $image = UploadedFile::fake()->image('test.jpg');
        $imagePath = $image->storeAs('products', 'test.jpg', 'public');
        
        $product = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'image_url' => $imagePath,
            'price' => 100.00,
        ]);

        // Update project total to include the product
        $this->project->update(['total_amount' => 100.00]);

        $result = $this->productService->deleteProduct($product);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($imagePath);
        
        $this->project->refresh();
        $this->assertEquals(0.00, $this->project->total_amount);
    }

    public function test_delete_product_prevents_deletion_with_contributions()
    {
        $product = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
        ]);

        // Create a contribution for the project
        Contribution::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot delete product that is referenced by existing contributions.');

        $this->productService->deleteProduct($product);
    }

    public function test_reorder_products_updates_sort_order()
    {
        $product1 = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
        ]);

        $product2 = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 2,
        ]);

        $product3 = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 3,
        ]);

        // Reorder: product3, product1, product2
        $newOrder = [$product3->id, $product1->id, $product2->id];

        $this->productService->reorderProducts($this->project, $newOrder);

        $product1->refresh();
        $product2->refresh();
        $product3->refresh();

        $this->assertEquals(2, $product1->sort_order);
        $this->assertEquals(3, $product2->sort_order);
        $this->assertEquals(1, $product3->sort_order);
    }

    public function test_reorder_products_validates_empty_order()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order array cannot be empty.');

        $this->productService->reorderProducts($this->project, []);
    }

    public function test_upload_product_image_validates_file_size()
    {
        Storage::fake('public');
        
        // Create a fake file that's too large (3MB)
        $largeImage = UploadedFile::fake()->create('large.jpg', 3072, 'image/jpeg');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file size cannot exceed 2MB.');

        $this->productService->uploadProductImage($largeImage);
    }

    public function test_upload_product_image_validates_file_type()
    {
        Storage::fake('public');
        
        $textFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image must be a JPEG, PNG, GIF, or WebP file.');

        $this->productService->uploadProductImage($textFile);
    }

    public function test_upload_product_image_stores_file_successfully()
    {
        Storage::fake('public');
        
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        $path = $this->productService->uploadProductImage($image);

        $this->assertStringStartsWith('products/', $path);
        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_delete_product_image_removes_file()
    {
        Storage::fake('public');
        
        $image = UploadedFile::fake()->image('test.jpg');
        $path = $image->storeAs('products', 'test.jpg', 'public');

        $result = $this->productService->deleteProductImage($path);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_delete_product_image_handles_full_url()
    {
        Storage::fake('public');
        
        $image = UploadedFile::fake()->image('test.jpg');
        $path = $image->storeAs('products', 'test.jpg', 'public');
        $fullUrl = asset('storage/' . $path);

        $result = $this->productService->deleteProductImage($fullUrl);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_get_project_products_returns_ordered_products()
    {
        $product1 = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 2,
            'name' => 'Second Product',
        ]);

        $product2 = Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'sort_order' => 1,
            'name' => 'First Product',
        ]);

        $products = $this->productService->getProjectProducts($this->project);

        $this->assertCount(2, $products);
        $this->assertEquals('First Product', $products->first()->name);
        $this->assertEquals('Second Product', $products->last()->name);
    }

    public function test_calculate_project_product_total_sums_prices()
    {
        Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'price' => 100.00,
        ]);

        Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'price' => 150.50,
        ]);

        $total = $this->productService->calculateProjectProductTotal($this->project);

        $this->assertEquals(250.50, $total);
    }

    public function test_cleanup_unused_images_removes_orphaned_files()
    {
        Storage::fake('public');
        
        // Create some test images
        $usedImage = UploadedFile::fake()->image('used.jpg');
        $usedPath = $usedImage->storeAs('products', 'used.jpg', 'public');
        
        $unusedImage = UploadedFile::fake()->image('unused.jpg');
        $unusedPath = $unusedImage->storeAs('products', 'unused.jpg', 'public');

        // Create product that references the used image
        Product::factory()->create([
            'project_id' => $this->project->id,
            'tenant_id' => $this->tenant->id,
            'image_url' => $usedPath,
        ]);

        $cleanedCount = $this->productService->cleanupUnusedImages();

        $this->assertEquals(1, $cleanedCount);
        Storage::disk('public')->assertExists($usedPath);
        Storage::disk('public')->assertMissing($unusedPath);
    }
}