<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // appointment, sale, fixed
            $table->decimal('rate', 5, 2)->default(0); // yuzde
            $table->decimal('fixed_amount', 10, 2)->default(0); // sabit maas
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0); // kazanilan prim
            $table->string('period')->nullable(); // 2026-06 gibi yil-ay
            $table->string('status')->default('pending'); // pending, paid
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_commissions');
    }
};
