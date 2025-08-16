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
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->constrained('tenant_applications')->onDelete('set null');
            // status already exists, skip it
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspended_reason')->nullable();
            $table->foreignId('suspended_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'application_id')) {
                $table->dropForeign(['application_id']);
                $table->dropColumn('application_id');
            }
            if (Schema::hasColumn('tenants', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
            if (Schema::hasColumn('tenants', 'suspended_reason')) {
                $table->dropColumn('suspended_reason');
            }
            if (Schema::hasColumn('tenants', 'suspended_by')) {
                $table->dropForeign(['suspended_by']);
                $table->dropColumn('suspended_by');
            }
        });
    }
};
