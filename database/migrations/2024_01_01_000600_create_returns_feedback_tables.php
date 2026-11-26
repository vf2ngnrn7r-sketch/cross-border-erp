<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 退货 / A-to-Z / 买家反馈
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('return_id')->nullable();
            $table->string('asin', 20)->nullable();
            $table->string('seller_sku')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->default('requested'); // requested|approved|received|refunded|rejected
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('return_date')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('a_to_z_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('claim_id')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->default('open'); // open|under_review|granted|denied
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('comments')->nullable();
            $table->timestamp('feedback_date')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_feedback');
        Schema::dropIfExists('a_to_z_claims');
        Schema::dropIfExists('returns');
    }
};
