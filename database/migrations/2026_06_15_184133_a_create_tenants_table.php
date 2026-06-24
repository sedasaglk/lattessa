<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('subdomain')->nullable()->unique();
            $table->string('company_name');
            $table->string('business_type');
            $table->string('owner_name');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->unsignedBigInteger('current_package_id')->nullable();
            $table->string('timezone')->default('Europe/Istanbul');
            $table->string('currency')->default('TRY');
            $table->string('theme')->default('light');
            $table->string('logo_path')->nullable();
            $table->string('referral_code')->unique()->nullable();
            $table->unsignedBigInteger('referred_by_tenant_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
