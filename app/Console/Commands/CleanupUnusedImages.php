<?php

namespace App\Console\Commands;

use App\Services\ProductService;
use Illuminate\Console\Command;

class CleanupUnusedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up unused product images from storage';

    /**
     * Execute the console command.
     */
    public function handle(ProductService $productService): int
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Starting image cleanup process...');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
        }

        // Get count of images that would be cleaned up
        if ($isDryRun) {
            // For dry run, we'll simulate the cleanup
            $cleanedCount = $this->simulateCleanup();
        } else {
            if (! $force && ! $this->confirm('Are you sure you want to delete unused images? This action cannot be undone.')) {
                $this->info('Operation cancelled.');

                return Command::SUCCESS;
            }

            $cleanedCount = $productService->cleanupUnusedImages();
        }

        if ($cleanedCount > 0) {
            $action = $isDryRun ? 'would be deleted' : 'deleted';
            $this->info("✅ {$cleanedCount} unused image(s) {$action}.");
        } else {
            $this->info('✅ No unused images found.');
        }

        return Command::SUCCESS;
    }

    /**
     * Simulate cleanup for dry run mode
     */
    private function simulateCleanup(): int
    {
        $allImages = \Storage::disk('public')->files('products');
        $unusedCount = 0;

        $this->info('Checking images...');

        foreach ($allImages as $imagePath) {
            $exists = \App\Models\Product::where('image_url', $imagePath)->exists();

            if (! $exists) {
                $this->line("  - Would delete: {$imagePath}");
                $unusedCount++;
            }
        }

        return $unusedCount;
    }
}
