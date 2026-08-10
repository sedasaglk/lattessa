<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('booking_slug')->nullable()->after('name');
        });

        // Mevcut şubelere slug ata
        DB::table('branches')->whereNull('booking_slug')->get()->each(function ($branch) {
            $slug = Str::slug($branch->name, '-');
            if (!$slug) $slug = 'sube-' . $branch->id;
            // Aynı tenant'ta tekrar ederse ID ekle
            $exists = DB::table('branches')
                ->where('tenant_id', $branch->tenant_id)
                ->where('booking_slug', $slug)
                ->exists();
            if ($exists) $slug .= '-' . $branch->id;
            DB::table('branches')->where('id', $branch->id)->update(['booking_slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('booking_slug');
        });
    }
};
