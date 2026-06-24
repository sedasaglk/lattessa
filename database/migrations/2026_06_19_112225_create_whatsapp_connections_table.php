<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable(); // null = sistem genel baglanti
            $table->string('reg_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('platform')->nullable();
            $table->string('user_name')->nullable();
            $table->string('status')->default('disconnected'); // disconnected, pending, connected
            $table->string('pairing_code')->nullable();
            $table->text('qr_code')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->string('type')->default('general'); // appointment_reminder, campaign, birthday, general
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->unsignedBigInteger('report_id')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('whatsapp_connections');
    }
};
