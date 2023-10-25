<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = "keranjang";

    protected $guarded = [
        "id"
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function barangKeranjangs() : HasMany
    {
        return $this->hasMany(BarangOrder::class);
    }
}
