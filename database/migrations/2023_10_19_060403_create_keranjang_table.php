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
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('pembeli_id');
            $table->foreignId('pembeli_id')->references('id')->on('users')->onDelete('cascade');
            $table->string("nama_pembeli");
            $table->string("kelas");
            $table->string("ruang_kelas")->nullable();
            $table->string("no_hp");
            $table->string("status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};
