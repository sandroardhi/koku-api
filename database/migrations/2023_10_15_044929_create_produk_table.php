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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string("foto")->nullable();
            $table->string("nama");
            $table->integer("harga");
            $table->integer("kuantitas");
            $table->foreignId('kantin_id')->references('id')->on('kantin')->onDelete('cascade');
            $table->foreignId('penjual_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('kategori_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
