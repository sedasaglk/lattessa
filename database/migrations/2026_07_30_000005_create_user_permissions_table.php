<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('permission'); // sales, cash, staff, vb.
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
