<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_tenant_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('role'); // tenant_admin, project_manager
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure a user can only have one role per tenant
            $table->unique(['user_id', 'tenant_id']);
            
            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['tenant_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tenant_roles');
    }
};