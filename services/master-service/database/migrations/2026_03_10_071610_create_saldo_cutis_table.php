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
        Schema::create('saldo_cutis', function (Blueprint $table) {
            $table->id();
            $table->string('jenis');
            $table->integer('jumlah');
            $table->foreignId('jabatan_id')->constrained('jabatans')->cascadeOnDelete();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_cutis');
    }
};
