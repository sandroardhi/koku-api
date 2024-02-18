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

            // logic for associating pengantar with orders
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

            // logic for changing the status_uang in OrderBarang
            if ($order->status == 'Selesai') {
                $orderBarangs = $order->orderBarangs;

                foreach ($orderBarangs as $orderBarang) {
                    if ($orderBarang->status_uang == 'Proses') {
                        if ($orderBarang->status == 'Selesai') {
                            if ($order->tipe_pembayaran == 'Online') {
                                $orderBarang->status_uang = 'Sukses';
                            } else {
                                $orderBarang->status_uang = 'Selesai';
                            }
                        } elseif ($orderBarang->status == 'Gagal Dibuat') {
                            if ($order->tipe_pembayaran == 'Online') {
                                $orderBarang->status_uang = 'Refund';
                            } else {
                                $orderBarang->status_uang = 'Gagal';
                            }
                        }
                        $orderBarang->save();
                    }
                }

                if ($order->tipe_pengiriman == 'Antar' && $order->status_ongkir == 'Proses') {
                    $order->status_ongkir = 'Sukses';
                    $order->save();
                }
            }

            if ($order->status == 'Canceled') {
                $orderBarangs = $order->orderBarangs;

                foreach ($orderBarangs as $orderBarang) {
                    if ($orderBarang->status_uang == 'Proses') {
                        $orderBarang->status_uang = 'Gagal';
                        if ($order->tipe_pembayaran == 'Online') {
                            $orderBarang->status_uang = 'Refund';
                        }
                        $orderBarang->save();
                    }
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
