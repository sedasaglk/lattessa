<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('appointment_id');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
        });

        // Mevcut appointment kayıtlarını güncelle
        \Illuminate\Support\Facades\DB::statement("
            UPDATE cash_transactions
            SET reference_type = 'appointment', reference_id = appointment_id
            WHERE appointment_id IS NOT NULL AND reference_type IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropColumn(['reference_type', 'reference_id']);
        });
    }
};
