<?php

namespace App\Models;

use App\Observers\OrderBarangObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OrderBarang extends Model
{
    use HasFactory;

    protected $table = "order_barang";

    protected $guarded = [
        "id"
    ];

    protected static function booted()
    {
        static::updated(function ($orderBarang) {
            // Log::info('Updated observer triggered');
            $order = $orderBarang->order;

            $allCompleted = $order->orderBarangs()->whereIn('status', ['Selesai', 'Gagal Dibuat'])->count() === $order->orderBarangs()->count();
            $allFailed = $order->orderBarangs()->where('status', 'Gagal Dibuat')->count() === $order->orderBarangs()->count();
            // Log::info(['check all completed.', $allCompleted]);
            // Log::info(['check all failed.', $allFailed]);

            if($order->status !== 'Selesai')
            {
                if ($allCompleted) {
                    if ($allFailed) {
                        $order->status = 'Canceled';
                        $order->save();
                        // Log::info(['From inside of allFailed, check $order.', $order]);
                        if ($order->pengantar_id !== null) {
                            $pengantar = User::find($order->pengantar_id);
                            if ($pengantar) {
                                $pengantar->pengantarIsAvailable = 'active';
                                $pengantar->save();
                                // Log::info(['From inside of allFailed and $pengantar is true, check $pengantar.', $pengantar]);
                                // Log::info(['order status updated all failed.', $allFailed]);
                            }
                        }
                    } else {
                        if ($order->tipe_pengiriman == 'Pick-up') {
                            $order->status = 'Konfirmasi Pembeli';
                            $order->save();
                            // Log::info(['From inside of allCompleted (not allFailed) tipe_pengiriman = Pick-up, check $order.', $order, 'Check allCompleted state', $allCompleted, 'Check allFailed state', $allFailed]);
                        } else {
                            $order->status = 'Dikirim';
                            $order->save();
                            // Log::info(['From inside of allCompleted (not allFailed) tipe_pengiriman = Antar, check $order.', $order, 'Check allCompleted state', $allCompleted, 'Check allFailed state', $allFailed]);
                        }
                    }
                }
            }
        });
    }
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

    public function kantin()
    {
        return $this->belongsTo(Kantin::class, 'kantin_id');
    }
}
