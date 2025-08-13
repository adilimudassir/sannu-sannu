<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add optimized index for product ordering within projects
            $table->dropIndex(['project_id', 'sort_order']);
            $table->index(['project_id', 'sort_order'], 'idx_products_project_sort');
        });
        
        // Add constraint to ensure price > 0 (requirement 7.2)
        // Note: SQLite doesn't support adding constraints after table creation, so we'll skip for now
        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_price CHECK (price > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraint
        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS chk_products_price');
        }
        
        Schema::table('products', function (Blueprint $table) {
            // Restore original index
            $table->dropIndex('idx_products_project_sort');
            $table->index(['project_id', 'sort_order']);
        });
    }
};
