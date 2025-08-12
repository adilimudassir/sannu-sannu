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
        // For SQLite, we need to recreate the table to remove tenant_id properly
        
        // First, create a temporary table with the new structure
        Schema::create('users_new', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique(); // Global unique email
            $table->string('password');
            $table->enum('role', ['system_admin', 'contributor'])->default('contributor');
            $table->string('phone', 20)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('is_active');
        });

        // Copy data from old table to new table (excluding tenant_id)
        DB::statement('
            INSERT INTO users_new (id, name, email, password, role, phone, avatar_url, bio, is_active, email_verified_at, last_login_at, remember_token, created_at, updated_at)
            SELECT id, name, email, password, 
                   CASE 
                       WHEN role = "tenant_admin" THEN "contributor"
                       WHEN role = "project_manager" THEN "contributor"
                       ELSE role
                   END as role,
                   phone, avatar_url, bio, is_active, email_verified_at, last_login_at, remember_token, created_at, updated_at
            FROM users
        ');

        // Drop the old table
        Schema::dropIfExists('users');

        // Rename the new table
        Schema::rename('users_new', 'users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the original users table structure
        Schema::create('users_old', function (Blueprint $table) {
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

        // Copy data back (this is a simplified rollback - in production you'd need to handle tenant assignment)
        DB::statement('
            INSERT INTO users_old (id, tenant_id, name, email, password, role, phone, avatar_url, bio, is_active, email_verified_at, last_login_at, remember_token, created_at, updated_at)
            SELECT id, 1 as tenant_id, name, email, password, role, phone, avatar_url, bio, is_active, email_verified_at, last_login_at, remember_token, created_at, updated_at
            FROM users
        ');

        Schema::dropIfExists('users');
        Schema::rename('users_old', 'users');
    }
};