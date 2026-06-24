<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('complaint')->nullable();       // sikayet/basvuru nedeni
            $table->text('anamnesis')->nullable();         // anamnez
            $table->text('diagnosis')->nullable();         // tani
            $table->text('treatment_plan')->nullable();    // tedavi plani
            $table->json('allergies')->nullable();         // alerjiler
            $table->json('medications')->nullable();       // kullaniilan ilaclar
            $table->decimal('height', 5, 2)->nullable();  // boy (cm)
            $table->decimal('weight', 5, 2)->nullable();  // kilo (kg)
            $table->string('blood_type')->nullable();      // kan grubu
            $table->text('medical_history')->nullable();   // gecmis hastaliklar
            $table->text('family_history')->nullable();    // aile hikayesi
            $table->text('notes')->nullable();             // genel notlar
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->unique(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_files');
    }
};
