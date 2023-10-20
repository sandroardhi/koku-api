<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangOrder extends Model
{
    use HasFactory;

    protected $table = "barang_order";
    
    protected $guarded = [
        "id"
    ];
}
