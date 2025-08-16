<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use RuntimeException;

class ImageService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver);
    }

    /**
     * Upload and process a product image
     */
    public function uploadProductImage(UploadedFile $file): string
    {
        $this->validateImageFile($file);

        $filename = $this->generateUniqueFilename($file);
        $path = "products/{$filename}";

        // Process and optimize the image
        $processedImage = $this->processImage($file);

        // Store the processed image
        $stored = Storage::disk('public')->put($path, $processedImage);

        if (! $stored) {
            throw new RuntimeException('Failed to upload product image.');
        }

        return $path;
    }

    /**
     * Process and optimize an image
     */
    private function processImage(UploadedFile $file): string
    {
        $image = $this->imageManager->read($file->getPathname());

        // Resize if too large (max 800x600 while maintaining aspect ratio)
        if ($image->width() > 800 || $image->height() > 600) {
            $image->scale(width: 800, height: 600);
        }

        // Optimize quality
        return $image->toJpeg(quality: 85)->toString();
    }

    /**
     * Generate a unique filename for the uploaded image
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        // Convert to jpg for consistency and optimization
        if (in_array(strtolower($extension), ['png', 'gif', 'webp'])) {
            $extension = 'jpg';
        }

        return Str::uuid().'.'.$extension;
    }

    /**
     * Delete an image from storage
     */
    public function deleteImage(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Extract path from full URL if needed
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($path, PHP_URL_PATH);
            $path = ltrim($parsedUrl, '/storage/');
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Get the full URL for an image
     */
    public function getImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Generate storage URL
        return Storage::disk('public')->url($path);
    }

    /**
     * Check if an image exists in storage
     */
    public function imageExists(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions(string $path): ?array
    {
        if (! $this->imageExists($path)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($path);
        $imageSize = getimagesize($fullPath);

        if (! $imageSize) {
            return null;
        }

        return [
            'width' => $imageSize[0],
            'height' => $imageSize[1],
            'mime' => $imageSize['mime'] ?? null,
        ];
    }

    /**
     * Validate uploaded image file
     */
    private function validateImageFile(UploadedFile $file): void
    {
        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Image file size cannot exceed 5MB.');
        }

        // Check file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            throw new InvalidArgumentException('Image must be a JPEG, PNG, GIF, or WebP file.');
        }

        // Check if file is actually an image
        if (! getimagesize($file->getPathname())) {
            throw new InvalidArgumentException('Uploaded file is not a valid image.');
        }

        // Check image dimensions (minimum 100x100)
        $imageSize = getimagesize($file->getPathname());
        if ($imageSize[0] < 100 || $imageSize[1] < 100) {
            throw new InvalidArgumentException('Image must be at least 100x100 pixels.');
        }

        // Check for potential security issues
        $this->validateImageSecurity($file);
    }

    /**
     * Additional security validation for images
     */
    private function validateImageSecurity(UploadedFile $file): void
    {
        // Check for embedded PHP code in image files
        $content = file_get_contents($file->getPathname());

        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            throw new InvalidArgumentException('Image file contains potentially malicious content.');
        }

        // Validate file extension matches MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        $validCombinations = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
        ];

        if (! isset($validCombinations[$extension]) ||
            ! in_array($mimeType, $validCombinations[$extension])) {
            throw new InvalidArgumentException('File extension does not match the file type.');
        }
    }

    /**
     * Clean up unused images in a directory
     */
    public function cleanupUnusedImages(string $directory, callable $usageChecker): int
    {
        $cleanedCount = 0;
        $allImages = Storage::disk('public')->files($directory);

        foreach ($allImages as $imagePath) {
            if (! $usageChecker($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                $cleanedCount++;
            }
        }

        return $cleanedCount;
    }

    /**
     * Generate responsive image sizes
     */
    public function generateResponsiveImages(string $originalPath): array
    {
        if (! $this->imageExists($originalPath)) {
            throw new InvalidArgumentException('Original image does not exist.');
        }

        $fullPath = Storage::disk('public')->path($originalPath);
        $image = $this->imageManager->read($fullPath);

        $sizes = [
            'thumbnail' => ['width' => 150, 'height' => 150],
            'small' => ['width' => 300, 'height' => 300],
            'medium' => ['width' => 600, 'height' => 600],
        ];

        $generatedImages = [];
        $pathInfo = pathinfo($originalPath);

        foreach ($sizes as $sizeName => $dimensions) {
            $resizedImage = clone $image;
            $resizedImage->scale(
                width: $dimensions['width'],
                height: $dimensions['height']
            );

            $resizedPath = $pathInfo['dirname'].'/'.
                          $pathInfo['filename'].'_'.$sizeName.'.'.
                          $pathInfo['extension'];

            $stored = Storage::disk('public')->put(
                $resizedPath,
                $resizedImage->toJpeg(quality: 85)->toString()
            );

            if ($stored) {
                $generatedImages[$sizeName] = $resizedPath;
            }
        }

        return $generatedImages;
    }
}
