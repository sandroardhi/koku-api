<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderBarang;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderBarangObserver
{
    /**
     * Handle the OrderBarang "created" event.
     */
    public function created(OrderBarang $orderBarang): void
    {
        //
    }

    /**
     * Handle the OrderBarang "updated" event.
     */
    public function updated(OrderBarang $orderBarang): void
    {
        Log::info('Updated observer triggered');
        $order = $orderBarang->order;

        $allCompleted = $order->orderBarangs()->whereIn('status', ['Selesai', 'Gagal Dibuat'])->count() === $order->orderBarangs()->count();
        $allFailed = $order->orderBarangs()->where('status', 'Gagal Dibuat')->count() === $order->orderBarangs()->count();
        Log::info(['check all completed.', $allCompleted]);
        Log::info(['check all failed.', $allFailed]);

        if ($allCompleted) {
            if ($allFailed) {
                $order->status = 'Selesai';
                $order->save();
                Log::info(['From inside of allFailed, check $order.', $order]);
                if ($order->pengantar_id !== null) {
                    $pengantar = User::find($order->pengantar_id);
                    if ($pengantar) {
                        $pengantar->pengantarIsAvailable = 'active';
                        $pengantar->save();
                        Log::info(['From inside of allFailed and $pengantar is true, check $pengantar.', $pengantar]);
                        Log::info(['order status updated all failed.', $allFailed]);
                    }
                }
            } else {
                if ($order->tipe_pengiriman == 'Pick-up') {
                    $order->status = 'Konfirmasi Pembeli';
                    $order->save();
                    Log::info(['From inside of allCompleted (not allFailed) tipe_pengiriman = Pick-up, check $order.', $order, 'Check allCompleted state', $allCompleted, 'Check allFailed state', $allFailed]);
                } else {
                    $order->status = 'Dikirim';
                    $order->save();
                    Log::info(['From inside of allCompleted (not allFailed) tipe_pengiriman = Antar, check $order.', $order, 'Check allCompleted state', $allCompleted, 'Check allFailed state', $allFailed]);
                }
            }
        }
    }

    /**
     * Handle the OrderBarang "deleted" event.
     */
    public function deleted(OrderBarang $orderBarang): void
    {
        //
    }

    /**
     * Handle the OrderBarang "restored" event.
     */
    public function restored(OrderBarang $orderBarang): void
    {
        //
    }

    /**
     * Handle the OrderBarang "force deleted" event.
     */
    public function forceDeleted(OrderBarang $orderBarang): void
    {
        //
    }
}
