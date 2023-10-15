<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kantin extends Model
{
    use HasFactory;

    protected $table = "kantin";

    protected $fillable = [
        'nama_kantin',
        "deskripsi",
        "ibu_kantin_id",
        "tabel_produk_id"
    ];
}
