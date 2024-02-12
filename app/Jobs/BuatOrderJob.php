<?php

namespace App\Jobs;

use App\Models\Keranjang;
use App\Models\Order;
use App\Models\OrderBarang;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class BuatOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;


    /**
     * Create a new job instance.
     */
    public function __construct(array $requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $requestData = $this->requestData;
        DB::transaction(
            function () use ($requestData) {

                $user = auth()->user();

                $order = Order::create([
                    'user_id' => $user->id,
                    'unique_string' => $requestData['unique_string'],
                    'total_harga' => $requestData['totalHarga'],
                    'tipe_pengiriman' => $requestData['tipePengiriman'],
                    'tipe_pembayaran' => $requestData['tipePembayaran'],
                    'tujuan' => $requestData['tujuan'],
                    'catatan' => $requestData['catatan'],
                    'ongkir' => $requestData['ongkir'],
                    'payment_status' => $requestData['payment_status'],
                ]);

                $pengantar = $requestData['pengantar'];

                if ($pengantar != null) {
                    Log::info($pengantar);
                    $pengantar->update(['pengantarIsAvailable' => 'ongoing']);
                    $order->pengantar()->associate($pengantar)->save();
                } else {
                    $order->pengantar()->dissociate()->save();
                }

                $produkData = $requestData['produkData'];

                foreach ($produkData as $barangKeranjang) {
                    $produk = Produk::where('id', $barangKeranjang["id"])->first();

                    $orderBarang = new OrderBarang([
                        'order_id' => $order->id,
                        'produk_id' => $produk->id,
                        'kantin_id' => $produk->kantin_id,
                        'nama' => $produk->nama,
                        'foto' => $produk->foto,
                        'harga'      => $barangKeranjang['harga'],
                        'kuantitas'   => $barangKeranjang['pivot']['kuantitas'],
                    ]);
                    $order->orderBarangs()->save($orderBarang);

                    $produk->update([
                        'stok' => $produk->stok - $barangKeranjang['pivot']['kuantitas']
                    ]);
                }

                if (($requestData['tipePembayaran'] == 'Online')) {
                    $order->snap_token = $requestData['snapToken'];
                    $order->save();
                }

                Keranjang::destroy($requestData['keranjang_id']);
            }
        );
    }
}
