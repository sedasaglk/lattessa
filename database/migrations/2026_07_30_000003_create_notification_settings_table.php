<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('event'); // appointment_confirmation, appointment_reminder_24h, appointment_reminder_2h, appointment_completed, review_request, birthday
            $table->boolean('enabled')->default(true);
            $table->string('channel')->default('auto'); // auto, whatsapp, sms, none
            $table->text('template')->nullable(); // null = default şablon
            $table->timestamps();
            $table->unique(['tenant_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
