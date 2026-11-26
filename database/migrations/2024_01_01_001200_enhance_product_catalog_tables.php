<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 深化商品管理：父子变体、类目/Browse Node、图片、Listing 状态增强
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('organization_id')
                ->constrained('products')->nullOnDelete();
            $table->string('product_type', 16)->default('standalone')->after('parent_id'); // parent|child|standalone
            $table->string('parent_sku')->nullable()->after('sku');
            $table->string('parent_asin', 20)->nullable()->after('asin');
            $table->string('browse_node_id')->nullable()->after('category');
            $table->string('browse_node_name')->nullable()->after('browse_node_id');
            $table->string('amazon_product_type')->nullable()->after('browse_node_name');
            $table->text('description')->nullable()->after('amazon_product_type');
            $table->json('bullet_points')->nullable()->after('description');
            $table->json('variation_theme')->nullable()->after('bullet_points'); // e.g. ["Color","Size"]
            $table->string('main_image_url')->nullable()->after('variation_theme');
            $table->decimal('default_cost', 12, 4)->nullable()->after('main_image_url');
            $table->decimal('default_price', 12, 2)->nullable()->after('default_cost');
            $table->string('default_currency', 3)->default('USD')->after('default_price');
            $table->index(['organization_id', 'parent_id']);
            $table->index(['organization_id', 'product_type']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('variant')->nullable(); // MAIN|PT01|SWATCH
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_main')->default(false);
            $table->timestamps();
            $table->index(['product_id', 'position']);
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('browse_node_id')->nullable();
            $table->string('name');
            $table->string('path')->nullable(); // Electronics > Headphones
            $table->string('amazon_product_type')->nullable();
            $table->json('attribute_schema')->nullable(); // 建议属性键
            $table->timestamps();
            $table->unique(['organization_id', 'browse_node_id']);
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->string('listing_status', 32)->nullable()->after('status'); // BUYABLE|DISCOVERABLE|SUPPRESSED|INACTIVE
            $table->string('suppress_reason')->nullable()->after('listing_status');
            $table->json('status_flags')->nullable()->after('suppress_reason'); // ["BUYABLE","DISCOVERABLE"]
            $table->string('main_image_url')->nullable()->after('status_flags');
            $table->integer('quantity')->nullable()->after('main_image_url'); // 与库存挂钩的展示数量
            $table->timestamp('open_date')->nullable()->after('quantity');
            $table->json('attributes')->nullable()->after('open_date');
            $table->string('amazon_product_type')->nullable()->after('attributes');
            $table->string('browse_node_id')->nullable()->after('amazon_product_type');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('listing_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'listing_status', 'suppress_reason', 'status_flags', 'main_image_url',
                'quantity', 'open_date', 'attributes', 'amazon_product_type', 'browse_node_id',
            ]);
        });

        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_images');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'product_type', 'parent_sku', 'parent_asin', 'browse_node_id', 'browse_node_name',
                'amazon_product_type', 'description', 'bullet_points', 'variation_theme',
                'main_image_url', 'default_cost', 'default_price', 'default_currency',
            ]);
        });
    }
};
