<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 买家消息 / 客服 Case（Messaging / Case Management 桩） */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('amazon_order_id')->nullable();
            $table->string('external_message_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('direction', 16)->default('inbound'); // inbound|outbound
            $table->string('status')->default('unread'); // unread|read|replied|archived
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('support_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amazon_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('case_id');
            $table->string('case_type')->nullable(); // buyer_message|account_health|listing|other
            $table->string('status')->default('open'); // open|pending|resolved|closed
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['amazon_account_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_cases');
        Schema::dropIfExists('buyer_messages');
    }
};
