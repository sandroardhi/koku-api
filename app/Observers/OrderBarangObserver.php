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
        $order = $orderBarang->order;

        // Check if all OrderBarang items in the associated Order have a status of 'Selesai' or 'Gagal Dibuat'
        $allCompleted = $order->orderBarangs()->whereIn('status', ['Selesai', 'Gagal Dibuat'])->count() === $order->orderBarangs()->count();
        // Check if all OrderBarang items in the associated Order have a status of 'Gagal Dibuat'
        $allFailed = $order->orderBarangs()->where('status', 'Gagal Dibuat')->count() === $order->orderBarangs()->count();

        if ($allCompleted) {
            // All OrderBarang items are marked 'Selesai' or 'Gagal Dibuat'
            if ($order->tipe_pengiriman == 'Pick-up') {
                $order->update(['status' => 'Konfirmasi Pembeli']);
            } else {
                $order->update(['status' => 'Dikirim']);
            }
        } elseif ($allFailed) {
            $order->update(['status' => 'Selesai']);
            if ($order->pengantar_id !== null) {
                $pengantar = User::find($order->pengantar_id);
                if ($pengantar) {
                    $pengantar->update(['pengantarIsAvailable' => 'active']);
                    Log::info('Pengantar status updated for orderBarang update observer.');
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
