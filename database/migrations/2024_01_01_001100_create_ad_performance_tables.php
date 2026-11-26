<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 广告效果指标（Advertising reporting fixtures） */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ad_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_campaign_id')->nullable();
            $table->date('report_date');
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('cost', 14, 2)->default(0);
            $table->decimal('sales', 14, 2)->default(0);
            $table->unsignedInteger('orders')->default(0);
            $table->decimal('acos', 8, 4)->nullable(); // cost/sales
            $table->decimal('roas', 10, 4)->nullable(); // sales/cost
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
            $table->unique(
                ['amazon_account_id', 'external_campaign_id', 'report_date'],
                'ad_metrics_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_performance_metrics');
    }
};
