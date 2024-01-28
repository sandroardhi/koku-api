<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $table = 'keranjang';

    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'barang_keranjang', 'keranjang_id', 'produk_id')->withPivot('kuantitas');
    }   
}
