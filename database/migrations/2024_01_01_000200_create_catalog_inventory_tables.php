<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 商品目录 / Listing / 库存（FBA + FBM）
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('asin', 20)->nullable();
            $table->string('title');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->json('attributes')->nullable();
            $table->string('status')->default('draft'); // draft|active|archived
            $table->timestamps();
            $table->unique(['organization_id', 'sku']);
            $table->index(['organization_id', 'asin']);
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->string('seller_sku');
            $table->string('asin', 20)->nullable();
            $table->string('title')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('fulfillment_channel', 8)->default('FBM'); // FBA|FBM
            $table->string('status')->default('active'); // active|inactive|suppressed
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['amazon_account_id', 'marketplace_id', 'seller_sku'], 'listing_sku_unique');
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku');
            $table->string('asin', 20)->nullable();
            $table->string('fulfillment_channel', 8)->default('FBA'); // FBA|FBM
            $table->integer('quantity_available')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_inbound')->default(0);
            $table->integer('quantity_unfulfillable')->default(0);
            $table->string('warehouse_location')->nullable(); // FBM 本地仓 / FBA FC
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'sku']);
            $table->index(['amazon_account_id', 'fulfillment_channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('products');
    }
};
