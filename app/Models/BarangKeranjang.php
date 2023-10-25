<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangKeranjang extends Model
{
    use HasFactory;

    protected $table = "barang_keranjang";

    protected $guarded = [
        "id"
    ];

    public function keranjang() : BelongsTo
    {
        return $this->belongsTo(Keranjang::class);
    }
    public function produks() : HasMany
    {
        return $this->hasMany(Produk::class);
    }

}
