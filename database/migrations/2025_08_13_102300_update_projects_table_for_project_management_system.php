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
        Schema::table('projects', function (Blueprint $table) {
            // Add invite_only to visibility enum to support requirement 6
            $table->enum('visibility', ['public', 'private', 'invite_only'])->default('public')->change();
            
            // Add max_contributors field for requirement 7.6 (if it doesn't exist)
            if (!Schema::hasColumn('projects', 'max_contributors')) {
                $table->integer('max_contributors')->unsigned()->nullable()->after('requires_approval');
            }
            
            // Add optimized indexes for performance as specified in requirements
            $table->index(['tenant_id', 'status'], 'idx_projects_tenant_status');
            $table->index(['visibility', 'status'], 'idx_projects_visibility_status');
        });
        
        // Add check constraints using raw SQL (requirement 7.1 and 7.2)
        // Note: SQLite doesn't support adding constraints after table creation, so we'll skip for now
        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE projects ADD CONSTRAINT chk_projects_dates CHECK (end_date > start_date)');
            DB::statement('ALTER TABLE projects ADD CONSTRAINT chk_projects_amounts CHECK (total_amount > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraints using raw SQL
        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS chk_projects_dates');
            DB::statement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS chk_projects_amounts');
        }
        
        Schema::table('projects', function (Blueprint $table) {
            // Drop new indexes
            $table->dropIndex('idx_projects_tenant_status');
            $table->dropIndex('idx_projects_visibility_status');
            
            // Drop new columns if they exist
            if (Schema::hasColumn('projects', 'max_contributors')) {
                $table->dropColumn('max_contributors');
            }
            
            // Restore original enum values
            $table->enum('visibility', ['public', 'private'])->default('public')->change();
        });
    }
};
