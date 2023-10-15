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
        Schema::create('kantin', function (Blueprint $table) {
            $table->id();
            $table->string("nama_kantin");
            $table->text("deskripsi");
            $table->foreignId('ibu_kantin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('tabel_produk_id')->references('id')->on('produk')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantin');
    }
};
