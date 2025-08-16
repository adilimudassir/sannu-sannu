<?php

use App\Models\Product;
use App\Models\Project;
use App\Models\Tenant;
use App\Services\ImageService;
use App\Services\ProductService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->tenant = Tenant::factory()->create();
    $this->project = Project::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->imageService = app(ImageService::class);
    $this->productService = app(ProductService::class);
});

it('can add a product with image', function () {
    $file = UploadedFile::fake()->image('product.jpg', 800, 600);

    $productData = [
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 99.99,
        'image' => $file,
    ];

    $product = $this->productService->addProduct($this->project, $productData);

    expect($product->name)->toBe('Test Product');
    expect($product->price)->toBe('99.99');
    expect($product->image_url)->not->toBeNull();
    expect($product->image_url)->toStartWith('products/');

    Storage::disk('public')->assertExists($product->image_url);
});

it('can add a product without image', function () {
    $productData = [
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 99.99,
    ];

    $product = $this->productService->addProduct($this->project, $productData);

    expect($product->name)->toBe('Test Product');
    expect($product->image_url)->toBeNull();
});

it('can update a product with new image', function () {
    // Create product without image
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'image_url' => null,
    ]);

    $file = UploadedFile::fake()->image('new-product.jpg', 200, 200);

    $updateData = [
        'name' => 'Updated Product',
        'price' => 149.99,
        'image' => $file,
    ];

    $updatedProduct = $this->productService->updateProduct($product, $updateData);

    expect($updatedProduct->name)->toBe('Updated Product');
    expect($updatedProduct->image_url)->not->toBeNull();
    Storage::disk('public')->assertExists($updatedProduct->image_url);
});

it('can update a product and replace existing image', function () {
    // Create product with existing image
    $oldFile = UploadedFile::fake()->image('old-product.jpg', 200, 200);
    $oldPath = $this->imageService->uploadProductImage($oldFile);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'image_url' => $oldPath,
    ]);

    Storage::disk('public')->assertExists($oldPath);

    $newFile = UploadedFile::fake()->image('new-product.jpg', 200, 200);

    $updateData = [
        'name' => 'Updated Product',
        'price' => 149.99,
        'image' => $newFile,
    ];

    $updatedProduct = $this->productService->updateProduct($product, $updateData);

    expect($updatedProduct->image_url)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath); // Old image should be deleted
    Storage::disk('public')->assertExists($updatedProduct->image_url); // New image should exist
});

it('can remove image from product', function () {
    // Create product with image
    $file = UploadedFile::fake()->image('product.jpg', 200, 200);
    $imagePath = $this->imageService->uploadProductImage($file);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'image_url' => $imagePath,
    ]);

    Storage::disk('public')->assertExists($imagePath);

    $updateData = [
        'name' => 'Updated Product',
        'price' => 149.99,
        'remove_image' => true,
    ];

    $updatedProduct = $this->productService->updateProduct($product, $updateData);

    expect($updatedProduct->image_url)->toBeNull();
    Storage::disk('public')->assertMissing($imagePath); // Image should be deleted
});

it('deletes image when product is deleted', function () {
    // Create product with image
    $file = UploadedFile::fake()->image('product.jpg', 200, 200);
    $imagePath = $this->imageService->uploadProductImage($file);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'image_url' => $imagePath,
    ]);

    Storage::disk('public')->assertExists($imagePath);

    $deleted = $this->productService->deleteProduct($product);

    expect($deleted)->toBeTrue();
    Storage::disk('public')->assertMissing($imagePath);
});

it('can clean up unused product images', function () {
    // Create some images - some used, some unused
    $usedFile = UploadedFile::fake()->image('used.jpg', 200, 200);
    $unusedFile = UploadedFile::fake()->image('unused.jpg', 200, 200);

    $usedPath = $this->imageService->uploadProductImage($usedFile);
    $unusedPath = $this->imageService->uploadProductImage($unusedFile);

    // Create product that uses one image
    Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'project_id' => $this->project->id,
        'image_url' => $usedPath,
    ]);

    Storage::disk('public')->assertExists($usedPath);
    Storage::disk('public')->assertExists($unusedPath);

    $cleanedCount = $this->productService->cleanupUnusedImages();

    expect($cleanedCount)->toBe(1);
    Storage::disk('public')->assertExists($usedPath); // Used image should remain
    Storage::disk('public')->assertMissing($unusedPath); // Unused image should be deleted
});

it('can get product image url', function () {
    $file = UploadedFile::fake()->image('product.jpg', 200, 200);
    $imagePath = $this->imageService->uploadProductImage($file);

    $url = $this->productService->getProductImageUrl($imagePath);

    expect($url)->toContain('/storage/products/');
});

it('returns null for empty image path', function () {
    $url = $this->productService->getProductImageUrl('');

    expect($url)->toBeNull();
});
