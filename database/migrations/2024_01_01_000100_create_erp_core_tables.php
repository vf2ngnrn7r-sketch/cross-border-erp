<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 核心多租户 / 亚马逊账号 / 站点表
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('active'); // active|suspended
            $table->timestamps();
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member'); // owner|admin|member
            $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_organization_id')->nullable()->after('remember_token')
                ->constrained('organizations')->nullOnDelete();
        });

        Schema::create('marketplaces', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // 如 ATVPDKIKX0DER
            $table->string('country_code', 2);
            $table->string('name');
            $table->string('currency', 3);
            $table->string('domain')->nullable();
            $table->string('region', 8)->default('na'); // na|eu|fe
            $table->timestamps();
        });

        Schema::create('amazon_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('seller_id')->nullable(); // Amazon Merchant Token / Seller ID
            $table->string('status')->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('amazon_account_marketplace', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['amazon_account_id', 'marketplace_id'], 'aam_unique');
        });

        Schema::create('amazon_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            // 以下字段应通过 Laravel encrypted cast 存储，此处仅为占位结构
            $table->text('lwa_client_id')->nullable();
            $table->text('lwa_client_secret')->nullable();
            $table->text('lwa_refresh_token')->nullable();
            $table->text('aws_access_key_id')->nullable();
            $table->text('aws_secret_access_key')->nullable();
            $table->string('api_region', 8)->default('na');
            $table->string('role_arn')->nullable();
            $table->string('status')->default('draft'); // draft|active|invalid
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amazon_credentials');
        Schema::dropIfExists('amazon_account_marketplace');
        Schema::dropIfExists('amazon_accounts');
        Schema::dropIfExists('marketplaces');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_organization_id');
        });
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
