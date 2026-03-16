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
        Schema::create('grup_jam_kerjas', function (Blueprint $table) {
            // Exact schema from SQL - 57 fields
            $table->string('start', 5)->nullable();
            $table->string('end', 5)->nullable();
            $table->string('name', 256);
            $table->id();
            $table->string('min_check_in', 5)->nullable();
            $table->string('max_check_in', 5)->nullable();
            $table->string('min_check_out', 5)->nullable();
            $table->string('max_check_out', 5)->nullable();

            // Monday
            $table->string('monday_start', 5)->nullable();
            $table->string('monday_end', 5)->nullable();
            $table->string('monday_min_check_in', 5)->nullable();
            $table->string('monday_max_check_in', 5)->nullable();
            $table->string('monday_min_check_out', 5)->nullable();
            $table->string('monday_max_check_out', 5)->nullable();

            // Tuesday
            $table->string('tuesday_start', 5)->nullable();
            $table->string('tuesday_end', 5)->nullable();
            $table->string('tuesday_min_check_in', 5)->nullable();
            $table->string('tuesday_max_check_in', 5)->nullable();
            $table->string('tuesday_min_check_out', 5)->nullable();
            $table->string('tuesday_max_check_out', 5)->nullable();

            // Wednesday
            $table->string('wednesday_start', 5)->nullable();
            $table->string('wednesday_end', 5)->nullable();
            $table->string('wednesday_min_check_in', 5)->nullable();
            $table->string('wednesday_max_check_in', 5)->nullable();
            $table->string('wednesday_min_check_out', 5)->nullable();
            $table->string('wednesday_max_check_out', 5)->nullable();

            // Thursday
            $table->string('thursday_start', 5)->nullable();
            $table->string('thursday_end', 5)->nullable();
            $table->string('thursday_min_check_in', 5)->nullable();
            $table->string('thursday_max_check_in', 5)->nullable();
            $table->string('thursday_min_check_out', 5)->nullable();
            $table->string('thursday_max_check_out', 5)->nullable();

            // Friday
            $table->string('friday_start', 5)->nullable();
            $table->string('friday_end', 5)->nullable();
            $table->string('friday_min_check_in', 5)->nullable();
            $table->string('friday_max_check_in', 5)->nullable();
            $table->string('friday_min_check_out', 5)->nullable();
            $table->string('friday_max_check_out', 5)->nullable();

            // Saturday
            $table->string('saturday_start', 5)->nullable();
            $table->string('saturday_end', 5)->nullable();
            $table->string('saturday_min_check_in', 5)->nullable();
            $table->string('saturday_max_check_in', 5)->nullable();
            $table->string('saturday_min_check_out', 5)->nullable();
            $table->string('saturday_max_check_out', 5)->nullable();

            // Sunday
            $table->string('sunday_start', 5)->nullable();
            $table->string('sunday_end', 5)->nullable();
            $table->string('sunday_min_check_in', 5)->nullable();
            $table->string('sunday_max_check_in', 5)->nullable();
            $table->string('sunday_min_check_out', 5)->nullable();
            $table->string('sunday_max_check_out', 5)->nullable();

            // Day types
            $table->string('monday_type', 10)->nullable()->comment('WEEKDAY;WEEKEND;FULL;OFF');
            $table->string('tuesday_type', 10)->nullable()->comment('WEEKDAY;WEEKEND;FULL;OFF');
            $table->string('wednesday_type', 10)->nullable()->comment('WEEKDAY;WEEKEND;FULL;OFF');
            $table->string('thursday_type', 10)->nullable()->comment('WEEKDAY;WEEKEND;FULL;OFF');
            $table->string('friday_type', 10)->nullable()->comment('WEEKDAY;WEEKEND;FULL;OFF');
            $table->string('saturday_type', 10)->nullable()->comment('WEEKDAY;WEEKEND;FULL;OFF');
            $table->string('sunday_type', 10)->nullable()->comment('WEEKDAY;WEEKEND;FULL;OFF');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grup_jam_kerjas');
    }
};
