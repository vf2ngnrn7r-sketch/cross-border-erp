<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 仓配：仓库 / 货件 / 追踪（FBA 入库 + FBM 出库） */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type', 16)->default('local'); // local|fba|3pl
            $table->string('country', 2)->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipment_type', 32); // fba_inbound|fbm_outbound|transfer
            $table->string('external_shipment_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('status')->default('WORKING'); // WORKING|SHIPPED|IN_TRANSIT|RECEIVING|CLOSED|CANCELLED
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('destination_fulfillment_center')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('expected_arrival')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'shipment_type', 'status']);
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('asin', 20)->nullable();
            $table->string('fnsku')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('quantity_received')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('warehouses');
    }
};
