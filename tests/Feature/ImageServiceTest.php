<?php

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->imageService = app(ImageService::class);
});

it('can upload a product image', function () {
    $file = UploadedFile::fake()->image('product.jpg', 800, 600);

    $path = $this->imageService->uploadProductImage($file);

    expect($path)->toStartWith('products/');
    expect($path)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($path);
});

it('validates image file size', function () {
    // Create a file larger than 5MB
    $file = UploadedFile::fake()->create('large.jpg', 6000); // 6MB

    expect(fn () => $this->imageService->uploadProductImage($file))
        ->toThrow(InvalidArgumentException::class, 'Image file size cannot exceed 5MB.');
});

it('validates image file type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100);

    expect(fn () => $this->imageService->uploadProductImage($file))
        ->toThrow(InvalidArgumentException::class, 'Image must be a JPEG, PNG, GIF, or WebP file.');
});

it('can delete an image', function () {
    $file = UploadedFile::fake()->image('product.jpg', 200, 200);
    $path = $this->imageService->uploadProductImage($file);

    Storage::disk('public')->assertExists($path);

    $deleted = $this->imageService->deleteImage($path);

    expect($deleted)->toBeTrue();
    Storage::disk('public')->assertMissing($path);
});

it('can get image url', function () {
    $path = 'products/test-image.jpg';

    $url = $this->imageService->getImageUrl($path);

    expect($url)->toContain('/storage/products/test-image.jpg');
});

it('returns null for empty image path', function () {
    $url = $this->imageService->getImageUrl('');

    expect($url)->toBeNull();
});

it('can check if image exists', function () {
    $file = UploadedFile::fake()->image('product.jpg', 200, 200);
    $path = $this->imageService->uploadProductImage($file);

    expect($this->imageService->imageExists($path))->toBeTrue();
    expect($this->imageService->imageExists('non-existent.jpg'))->toBeFalse();
});

it('can clean up unused images', function () {
    // Create some test images
    $file1 = UploadedFile::fake()->image('used.jpg', 200, 200);
    $file2 = UploadedFile::fake()->image('unused.jpg', 200, 200);

    $usedPath = $this->imageService->uploadProductImage($file1);
    $unusedPath = $this->imageService->uploadProductImage($file2);

    // Mock usage checker - only first image is "used"
    $usageChecker = function ($path) use ($usedPath) {
        return $path === $usedPath;
    };

    $cleanedCount = $this->imageService->cleanupUnusedImages('products', $usageChecker);

    expect($cleanedCount)->toBe(1);
    Storage::disk('public')->assertExists($usedPath);
    Storage::disk('public')->assertMissing($unusedPath);
});
