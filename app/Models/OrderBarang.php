<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBarang extends Model
{
    use HasFactory;

    protected $table = "order_barang";

    protected $guarded = [
        "id"
    ];
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->isoFormat('D MMM YYYY H:mm:ss');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
