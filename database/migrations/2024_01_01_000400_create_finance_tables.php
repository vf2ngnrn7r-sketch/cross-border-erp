<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 财务结算 / 费用事件
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->string('settlement_id')->nullable();
            $table->timestamp('settlement_start')->nullable();
            $table->timestamp('settlement_end')->nullable();
            $table->timestamp('deposit_date')->nullable();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('open'); // open|closed|deposited
            $table->timestamps();
            $table->index(['organization_id', 'deposit_date']);
        });

        Schema::create('financial_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('settlement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type'); // Shipment|Refund|ServiceFee|Adjustment|etc
            $table->string('fee_type')->nullable(); // Commission|FBAPerUnitFulfillmentFee|etc
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('posted_date')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'event_type']);
            $table->index(['posted_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_events');
        Schema::dropIfExists('settlements');
    }
};
