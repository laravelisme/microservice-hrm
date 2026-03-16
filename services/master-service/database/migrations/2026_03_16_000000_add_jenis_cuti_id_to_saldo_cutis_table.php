<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saldo_cutis', function (Blueprint $table) {
            if (!Schema::hasColumn('saldo_cutis', 'jenis_cuti_id')) {
                $table->foreignId('jenis_cuti_id')->nullable()->constrained('jenis_cutis')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saldo_cutis', function (Blueprint $table) {
            if (Schema::hasColumn('saldo_cutis', 'jenis_cuti_id')) {
                $table->dropConstrainedForeignId('jenis_cuti_id');
            }
        });
    }
};
