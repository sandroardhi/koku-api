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
        Schema::create('order_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->references('id')->on('produk')->onDelete('cascade');
            $table->foreignId('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreignId('kantin_id')->references('id')->on('kantin')->onDelete('cascade');
            $table->string('nama');
            $table->string('foto');
            $table->double("harga");
            $table->enum('status', ['Menunggu Konfirmasi', 'Dibuat', 'Gagal Dibuat', 'Selesai'])->default('Menunggu Konfirmasi');
            $table->integer("kuantitas");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_order');
    }
};
