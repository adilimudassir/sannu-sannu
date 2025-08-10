<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->decimal('total_committed', 12, 2);
            $table->enum('payment_type', ['full', 'installments']);
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->enum('installment_frequency', ['monthly', 'quarterly', 'custom'])->nullable();
            $table->integer('total_installments')->nullable();
            $table->decimal('arrears_amount', 10, 2)->default(0.00);
            $table->decimal('arrears_paid', 10, 2)->default(0.00);
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->date('next_payment_due')->nullable();
            $table->enum('status', ['active', 'completed', 'suspended', 'cancelled'])->default('active');
            $table->date('joined_date');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'project_id']);
            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('project_id');
            $table->index('status');
            $table->index('approval_status');
            $table->index('next_payment_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
