<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class ProductService
{
    public function __construct(
        private ImageService $imageService
    ) {}

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
        return $this->imageService->uploadProductImage($file);
    }

    /**
     * Delete a product image from storage
     */
    public function deleteProductImage(string $path): bool
    {
        return $this->imageService->deleteImage($path);
    }

    /**
     * Get the full URL for a product image
     */
    public function getProductImageUrl(?string $path): ?string
    {
        return $this->imageService->getImageUrl($path);
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
        return $this->imageService->cleanupUnusedImages('products', function ($imagePath) {
            return Product::where('image_url', $imagePath)->exists();
        });
    }

    /**
     * Validate product data
     */
    private function validateProductData(array $data, ?Product $product = null): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Product name is required.');
        }

        if (! isset($data['price']) || ! is_numeric($data['price']) || $data['price'] < 0) {
            throw new InvalidArgumentException('Product price must be a valid positive number.');
        }

        // Additional validation for updates
        if ($product && isset($data['image']) && ! ($data['image'] instanceof UploadedFile)) {
            throw new InvalidArgumentException('Invalid image file provided.');
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
