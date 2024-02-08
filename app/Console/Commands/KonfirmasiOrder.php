<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class KonfirmasiOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:konfirmasi-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis konfirmasi order yang sudah satu hari berlum dikonfirmasi oleh pembeli';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::where('status', 'Konfirmasi Pembeli')
            ->where('created_at', '<=', Carbon::now()->subDay())
            ->get();

        foreach ($orders as $order) {
            $order->update(['status' => 'Selesai']);
            $this->info("Order {$order->id} has been marked as 'Selesai'");
        }
    }
}
