<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderBarang;
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
        if ($orderBarang->status == 'Dibuat') {
            // Check if all OrderBarang items in the associated Order have a status of 'Selesai'
            $order = $orderBarang->order;
            if ($order->orderBarangs()->where('status', '<>', 'Dibuat')->doesntExist()) {
                // All OrderBarang items are marked 'Selesai'
                $order->update(['status' => 'Proses']);
            }
        }
        if ($orderBarang->status == 'Selesai') {
            // Check if all OrderBarang items in the associated Order have a status of 'Selesai'
            $order = $orderBarang->order;
            if ($order->orderBarangs()->where('status', '<>', 'Selesai')->doesntExist()) {
                // All OrderBarang items are marked 'Selesai'
                $order->update(['status' => 'Konfirmasi Pembeli']);
            }
        }
        // Log::info("updated dari observer");
        // Log::info('Observer updated method called.', ['orderBarang_id' => $orderBarang->id]);
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
