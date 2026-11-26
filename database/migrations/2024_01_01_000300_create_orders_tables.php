<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 订单 / 订单行 / 履约状态
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('amazon_order_id')->unique();
            $table->timestamp('purchase_date')->nullable();
            $table->string('order_status')->default('Pending'); // Pending|Unshipped|PartiallyShipped|Shipped|Canceled|etc
            $table->string('fulfillment_channel', 16)->nullable(); // AFN|MFN
            $table->string('sales_channel')->nullable();
            $table->string('ship_service_level')->nullable();
            $table->decimal('order_total', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('buyer_email')->nullable();
            $table->json('shipping_address')->nullable();
            $table->string('payment_method')->nullable();
            $table->boolean('is_prime')->default(false);
            $table->timestamp('last_update_date')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'order_status']);
            $table->index(['amazon_account_id', 'purchase_date']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('order_item_id')->nullable();
            $table->string('asin', 20)->nullable();
            $table->string('seller_sku')->nullable();
            $table->string('title')->nullable();
            $table->unsignedInteger('quantity_ordered')->default(0);
            $table->unsignedInteger('quantity_shipped')->default(0);
            $table->decimal('item_price', 12, 2)->nullable();
            $table->decimal('shipping_price', 12, 2)->nullable();
            $table->decimal('item_tax', 12, 2)->nullable();
            $table->decimal('promotion_discount', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
            $table->index(['asin', 'seller_sku']);
        });

        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('shipment_id')->nullable();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('pending'); // pending|shipped|delivered|exception
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('estimated_arrival')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fulfillments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
