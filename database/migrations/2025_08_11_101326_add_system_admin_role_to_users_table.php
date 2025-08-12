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
        // For SQLite, we need to recreate the table with the new enum values
        if (DB::getDriverName() === 'sqlite') {
            // Disable foreign key checks
            DB::statement('PRAGMA foreign_keys=OFF');

            // Drop temp table if it exists from previous failed migration
            Schema::dropIfExists('users_temp');

            // Create a temporary table with the new enum values
            Schema::create('users_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->enum('role', ['system_admin', 'tenant_admin', 'project_manager', 'contributor'])->default('contributor');
                $table->string('phone', 20)->nullable();
                $table->string('avatar_url', 500)->nullable();
                $table->text('bio')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('role');
                $table->index('is_active');
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO users_temp SELECT * FROM users');

            // Drop the old table
            Schema::drop('users');

            // Rename the temporary table
            Schema::rename('users_temp', 'users');

            // Add unique constraint (can't be done in the same statement as table creation in SQLite)
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['tenant_id', 'email']);
            });

            // Re-enable foreign key checks
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            // For MySQL/PostgreSQL, we can use ALTER TABLE
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('system_admin', 'tenant_admin', 'project_manager', 'contributor') DEFAULT 'contributor'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite, we need to recreate the table with the old enum values
        if (DB::getDriverName() === 'sqlite') {
            // Create a temporary table with the old enum values
            Schema::create('users_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->enum('role', ['tenant_admin', 'project_manager', 'contributor'])->default('contributor');
                $table->string('phone', 20)->nullable();
                $table->string('avatar_url', 500)->nullable();
                $table->text('bio')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->unique(['tenant_id', 'email']);
                $table->index('tenant_id');
                $table->index('role');
                $table->index('is_active');
            });

            // Copy data from old table to new table (excluding system_admin users)
            DB::statement("INSERT INTO users_temp SELECT * FROM users WHERE role != 'system_admin'");

            // Drop the old table
            Schema::drop('users');

            // Rename the temporary table
            Schema::rename('users_temp', 'users');
        } else {
            // For MySQL/PostgreSQL, we can use ALTER TABLE
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('tenant_admin', 'project_manager', 'contributor') DEFAULT 'contributor'");
        }
    }
};