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
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembeli_id');
            $table->foreignId('pembeli_id')->references('id')->on('users')->onDelete('cascade');
            $table->string("nama");
            $table->enum("tipe_antar", ["ambil", "antar"]);
            $table->float("sub_total");
            $table->float("biaya_service");
            $table->float("grand_total");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
