<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    protected $guarded = [
        "id"
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($order) {
            if ($order->payment_status == 'paid' && $order->tipe_pengiriman == 'Antar' && !$order->pengantar_id) {
                // Log::info(['Order updated boot called']);
                $pengantar = User::role('pengantar')
                    ->where('pengantarIsAvailable', 'active')
                    ->first();

                if ($pengantar) {
                    $order->pengantar()->associate($pengantar);
                    $order->save();

                    $pengantar->update(['pengantarIsAvailable' => 'ongoing']);
                }
            }
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->isoFormat('D MMM YYYY H:mm:ss');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderBarangs()
    {
        return $this->hasMany(OrderBarang::class, 'order_id');
    }

    public function pengantar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengantar_id');
    }
}
