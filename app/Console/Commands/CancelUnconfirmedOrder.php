<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelUnconfirmedOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-unconfirmed-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel order yang tidak dikonfirmasi setelah 10 menit pembayaran dari Pelanggan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::whereHas('orderBarangs', function ($query) {
            $query->where('status', 'Menunggu Konfirmasi');
        })
            ->with('orderBarangs')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderBarangs as $orderBarang) {
                // Check if the orderBarang is unconfirmed for more than 30 minutes
                if ($orderBarang->status === 'Menunggu Konfirmasi' && now()->diffInMinutes($orderBarang->created_at) > 30) {
                    // Update the status of the orderBarang to 'Gagal Dibuat'
                    $orderBarang->update(['status' => 'Gagal Dibuat']);

                    Log::info('OrderBarang ' . $orderBarang->id . ' has been canceled due to unconfirmed status.');

                }
            }
        }
    }
}
