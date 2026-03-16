<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add nullable foreign key column
        Schema::table('saldo_cutis', function (Blueprint $table) {
            $table->foreignId('jenis_cuti_id')->nullable()->constrained('jenis_cutis')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saldo_cutis', function (Blueprint $table) {
            if (Schema::hasColumn('saldo_cutis', 'jenis_cuti_id')) {
                $table->dropForeign(['jenis_cuti_id']);
                $table->dropColumn('jenis_cuti_id');
            }
        });
    }
};
