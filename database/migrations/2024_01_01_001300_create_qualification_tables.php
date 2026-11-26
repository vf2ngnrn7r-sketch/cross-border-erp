<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 商品资质 + 店铺资质 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('asin', 20)->nullable();
            $table->string('sku')->nullable();
            $table->string('qualification_type', 64); // certificate|category_eligibility|safety_test|ce|fcc|fda|other
            $table->string('name');
            $table->string('document_number')->nullable();
            $table->string('issuer')->nullable();
            $table->string('status', 32)->default('valid'); // valid|expiring|expired|pending|rejected
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('file_url')->nullable();
            $table->string('external_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'expires_at']);
            $table->index(['asin', 'sku']);
        });

        Schema::create('store_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('qualification_type', 64); // brand_registry|category_approval|seller_identity|tax_info|business_license|ungating|other
            $table->string('name');
            $table->string('brand_name')->nullable();
            $table->string('category_name')->nullable();
            $table->string('document_number')->nullable();
            $table->string('status', 32)->default('approved'); // approved|pending|rejected|expiring|expired
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('file_url')->nullable();
            $table->string('external_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
            $table->index(['amazon_account_id', 'qualification_type']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_qualifications');
        Schema::dropIfExists('product_qualifications');
    }
};
