<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    use HasFactory;

    protected $table = "produk";

    protected $fillable = [
        "foto_produk",
        "nama_produk",
        "harga",
        "kuantitas",
        "penjual_id",
        "kantin_id"
    ];

    public function kantin() : BelongsTo
    {
        return $this->belongsTo(Kantin::class, "kantin_id");
    }
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, "penjual_id");
    }
}
