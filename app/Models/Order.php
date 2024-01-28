<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    protected $guarded = [
        "id"
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->isoFormat('D MMM YYYY');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderBarangs()
    {
        return $this->hasMany(OrderBarang::class, 'order_id');
    }
}
