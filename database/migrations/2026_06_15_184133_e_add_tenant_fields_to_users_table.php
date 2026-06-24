<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('musteri')->after('phone');
            $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            $table->string('avatar_path')->nullable();
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('status')->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tenant_id', 'phone', 'role', 'branch_id', 'avatar_path',
                'two_factor_secret', 'two_factor_enabled', 'status', 'last_login_at', 'deleted_at'
            ]);
        });
    }
};
