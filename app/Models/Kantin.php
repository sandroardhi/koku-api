<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kantin extends Model
{
    use HasFactory;

    protected $table = "kantin";

    protected $fillable = [
        'nama',
        "deskripsi",
        "penjual_id",
        "produk_id",
        'foto',
    ];

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(User::class, "penjual_id");
    }
    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, "kantin_id");
    }
}
