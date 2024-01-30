<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

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
        // Find orders with 'Menunggu Konfirmasi' status
        $orders = Order::where('status', 'Menunggu Konfirmasi')->get();
    
        foreach ($orders as $order) {
            // Check if the order is unconfirmed for more than 30 minutes
            if (now()->diffInMinutes($order->created_at) > 30) {
                // Update the order status to 'canceled'
                $order->update(['status' => 'Canceled']);
            }
        }
    
        $this->info('Unconfirmed orders canceled successfully.');
    }
    
}
