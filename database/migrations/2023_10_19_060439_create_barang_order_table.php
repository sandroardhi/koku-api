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
        Schema::create('barang_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->references('id')->on('produk')->onDelete('cascade');
            $table->foreignId('order_id')->references('id')->on('order')->onDelete('cascade');
            $table->float("harga");
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
