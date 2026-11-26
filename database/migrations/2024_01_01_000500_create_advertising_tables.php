<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 广告活动 / 广告组（Amazon Advertising API 桩）
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_campaign_id')->nullable();
            $table->string('name');
            $table->string('campaign_type', 8)->default('sp'); // sp|sb|sd
            $table->string('targeting_type', 16)->nullable(); // manual|auto
            $table->string('state')->default('enabled'); // enabled|paused|archived
            $table->decimal('daily_budget', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'state']);
        });

        Schema::create('ad_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('external_ad_group_id')->nullable();
            $table->string('name');
            $table->string('state')->default('enabled');
            $table->decimal('default_bid', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_groups');
        Schema::dropIfExists('ad_campaigns');
    }
};
