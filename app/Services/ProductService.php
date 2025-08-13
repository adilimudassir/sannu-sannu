<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Project;
use App\Models\Contribution;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ProductService
{
    /**
     * Add a new product to a project
     */
    public function addProduct(Project $project, array $data): Product
    {
        // Validate required fields
        $this->validateProductData($data);

        // Handle image upload if provided
        $imageUrl = null;
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $imageUrl = $this->uploadProductImage($data['image']);
        }

        // Get the next sort order
        $sortOrder = $this->getNextSortOrder($project);

        return DB::transaction(function () use ($project, $data, $imageUrl, $sortOrder) {
            $product = Product::create([
                'tenant_id' => $project->tenant_id,
                'project_id' => $project->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'image_url' => $imageUrl,
                'sort_order' => $sortOrder,
            ]);

            // Update project total amount
            $this->updateProjectTotal($project);

            return $product;
        });
    }

    /**
     * Update an existing product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        // Validate required fields
        $this->validateProductData($data, $product);

        $oldImageUrl = $product->image_url;
        $newImageUrl = $oldImageUrl;

        // Handle image upload if provided
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $newImageUrl = $this->uploadProductImage($data['image']);
        }

        // Handle image removal if explicitly requested
        if (isset($data['remove_image']) && $data['remove_image']) {
            $newImageUrl = null;
        }

        return DB::transaction(function () use ($product, $data, $oldImageUrl, $newImageUrl) {
            $product->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'image_url' => $newImageUrl,
            ]);

            // Clean up old image if it was replaced or removed
            if ($oldImageUrl && $oldImageUrl !== $newImageUrl) {
                $this->deleteProductImage($oldImageUrl);
            }

            // Update project total amount
            $this->updateProjectTotal($product->project);

            return $product->fresh();
        });
    }

    /**
     * Delete a product with contribution checks
     */
    public function deleteProduct(Product $product): bool
    {
        // Check if product is referenced by any contributions
        if ($this->hasContributionReferences($product)) {
            throw new RuntimeException('Cannot delete product that is referenced by existing contributions.');
        }

        return DB::transaction(function () use ($product) {
            $project = $product->project;
            $imageUrl = $product->image_url;

            // Delete the product
            $deleted = $product->delete();

            if ($deleted) {
                // Clean up image file
                if ($imageUrl) {
                    $this->deleteProductImage($imageUrl);
                }

                // Update project total amount
                $this->updateProjectTotal($project);
            }

            return $deleted;
        });
    }

    /**
     * Reorder products within a project
     */
    public function reorderProducts(Project $project, array $order): void
    {
        if (empty($order)) {
            throw new InvalidArgumentException('Order array cannot be empty.');
        }

        DB::transaction(function () use ($project, $order) {
            foreach ($order as $index => $productId) {
                Product::where('project_id', $project->id)
                    ->where('id', $productId)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    /**
     * Upload a product image and return the storage path
     */
    public function uploadProductImage(UploadedFile $file): string
    {
        // Validate file
        $this->validateImageFile($file);

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Store in products directory
        $path = $file->storeAs('products', $filename, 'public');

        if (!$path) {
            throw new RuntimeException('Failed to upload product image.');
        }

        return $path;
    }

    /**
     * Delete a product image from storage
     */
    public function deleteProductImage(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Extract path from full URL if needed
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path, PHP_URL_PATH);
            $path = ltrim($path, '/storage/');
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Get products for a project ordered by sort_order
     */
    public function getProjectProducts(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('project_id', $project->id)
            ->ordered()
            ->get();
    }

    /**
     * Calculate total price of all products in a project
     */
    public function calculateProjectProductTotal(Project $project): float
    {
        return Product::where('project_id', $project->id)
            ->sum('price');
    }

    /**
     * Clean up unused product images
     */
    public function cleanupUnusedImages(): int
    {
        $cleanedCount = 0;
        $allImages = Storage::disk('public')->files('products');
        
        foreach ($allImages as $imagePath) {
            $exists = Product::where('image_url', $imagePath)->exists();
            
            if (!$exists) {
                Storage::disk('public')->delete($imagePath);
                $cleanedCount++;
            }
        }

        return $cleanedCount;
    }

    /**
     * Validate product data
     */
    private function validateProductData(array $data, ?Product $product = null): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Product name is required.');
        }

        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
            throw new InvalidArgumentException('Product price must be a valid positive number.');
        }

        // Additional validation for updates
        if ($product && isset($data['image']) && !($data['image'] instanceof UploadedFile)) {
            throw new InvalidArgumentException('Invalid image file provided.');
        }
    }

    /**
     * Validate uploaded image file
     */
    private function validateImageFile(UploadedFile $file): void
    {
        // Check file size (max 2MB)
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('Image file size cannot exceed 2MB.');
        }

        // Check file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new InvalidArgumentException('Image must be a JPEG, PNG, GIF, or WebP file.');
        }

        // Check if file is actually an image
        if (!getimagesize($file->getPathname())) {
            throw new InvalidArgumentException('Uploaded file is not a valid image.');
        }
    }

    /**
     * Get the next sort order for a project
     */
    private function getNextSortOrder(Project $project): int
    {
        $maxOrder = Product::where('project_id', $project->id)
            ->max('sort_order');

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Check if product has contribution references
     */
    private function hasContributionReferences(Product $product): bool
    {
        // For now, we'll check if the project has any contributions
        // In a more complex system, you might have product-specific contributions
        return Contribution::where('project_id', $product->project_id)->exists();
    }

    /**
     * Update project total amount based on product prices
     */
    private function updateProjectTotal(Project $project): void
    {
        $total = $this->calculateProjectProductTotal($project);
        $project->update(['total_amount' => $total]);
    }
}