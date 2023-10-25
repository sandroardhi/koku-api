<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangOrder extends Model
{
    use HasFactory;

    protected $table = "barang_order";
    
    protected $guarded = [
        "id"
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function produks() : HasMany
    {
        return $this->hasMany(Produk::class);
    }

}
