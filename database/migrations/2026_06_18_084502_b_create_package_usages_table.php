<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->date('expires_at');
            $table->string('status')->default('active'); // active, expired, completed
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id', 'status']);
        });

        Schema::create('customer_package_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_package_id')->constrained('customer_packages')->cascadeOnDelete();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('used_by');
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_package_usages');
        Schema::dropIfExists('customer_packages');
    }
};
