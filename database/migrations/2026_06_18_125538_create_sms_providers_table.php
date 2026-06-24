<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sms_providers')) {
            Schema::create('sms_providers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('provider');
                $table->string('display_name')->default('');
                $table->text('credentials');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system_default')->default(false);
                $table->integer('priority')->default(1);
                $table->timestamps();
                $table->index(['tenant_id', 'is_active']);
            });
        } else {
            Schema::table('sms_providers', function (Blueprint $table) {
                if (!Schema::hasColumn('sms_providers', 'display_name')) {
                    $table->string('display_name')->after('provider')->default('');
                }
                if (!Schema::hasColumn('sms_providers', 'is_system_default')) {
                    $table->boolean('is_system_default')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('sms_providers', 'priority')) {
                    $table->integer('priority')->default(1)->after('is_system_default');
                }
            });
        }

        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('phone');
                $table->text('message');
                $table->string('type')->default('general');
                $table->string('provider')->nullable();
                $table->string('status')->default('pending');
                $table->json('provider_response')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_providers');
    }
};
