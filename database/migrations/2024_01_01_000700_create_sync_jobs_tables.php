<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 报表请求 / 同步任务骨架
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_type'); // orders|inventory|listings|finance|ads|returns|reports
            $table->string('report_type')->nullable(); // GET_FLAT_FILE_ALL_ORDERS_DATA_BY_ORDER_DATE_GENERAL 等
            $table->string('status')->default('pending'); // pending|running|succeeded|failed
            $table->json('payload')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'job_type', 'status']);
        });

        Schema::create('report_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sync_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_report_id')->nullable();
            $table->string('report_type');
            $table->string('processing_status')->default('SUBMITTED'); // SUBMITTED|IN_PROGRESS|DONE|CANCELLED|FATAL
            $table->string('document_id')->nullable();
            $table->json('request_options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_requests');
        Schema::dropIfExists('sync_jobs');
    }
};
