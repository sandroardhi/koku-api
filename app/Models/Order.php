<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = "order";

    protected $guarded = [
        "id"
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function barangOrders() : HasMany
    {
        return $this->hasMany(BarangOrder::class);
    }
}
