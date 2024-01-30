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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('unique_string', 10)->unique();
            $table->enum("tipe_pengiriman", ["Pick-up", "Antar"]);
            $table->enum("tipe_pembayaran", ["Cash", "Online"]);
            $table->double("total_harga");
            $table->enum('status', ['Menunggu Konfirmasi', 'Proses', 'Dikirim', 'Selesai', 'Canceled'])->default('Menunggu Konfirmasi');
            $table->enum('payment_status', ['pending', 'paid', 'canceled']);
            $table->timestamps();
            $table->string('tujuan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('snap_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
