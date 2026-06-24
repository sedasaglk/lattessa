<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('staff_id');
            $table->integer('session_number')->default(1);
            $table->date('session_date');
            $table->text('subjective')->nullable();    // S: Hasta ne soyluyor
            $table->text('objective')->nullable();     // O: Gozlemler/olcumler
            $table->text('assessment')->nullable();    // A: Degerlendirme
            $table->text('plan')->nullable();          // P: Plan (SOAP notu)
            $table->decimal('weight', 5, 2)->nullable(); // seans anindaki kilo
            $table->json('measurements')->nullable();  // olcumler (bel, kalca vb)
            $table->integer('mood_score')->nullable(); // ruh hali skoru (1-10)
            $table->text('homework')->nullable();      // ev odevi
            $table->text('next_session_plan')->nullable(); // sonraki seans plani
            $table->boolean('is_private')->default(false); // sadece doktor gorebilir
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_notes');
    }
};
