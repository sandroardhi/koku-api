<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Produk;
use App\Models\Keranjang;
use App\Models\Order;
use App\Models\OrderBarang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // START OF MIDTRANS INTEGRATION
    public function __construct()
    {
        \Midtrans\Config::$serverKey    = config('services.midtrans.serverKey');
        \Midtrans\Config::$isProduction = config('services.midtrans.isProduction');
        \Midtrans\Config::$isSanitized  = config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds        = config('services.midtrans.is3ds');
    }

    public function payAndCreateOrder(Request $request)
    {
        $requestData = $request->json()->all();

        Log::info($requestData);

        if ($requestData['tipePembayaran'] == 'Online') {
            DB::transaction(function () use ($requestData) {
                $user = auth()->user();

                if ($requestData['ongkir'] >= 0) {
                    $totalHarga = $requestData['totalHarga'] + $requestData['ongkir'];
                } else {
                    $totalHarga = $requestData['totalHarga'];
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'unique_string' => Str::random(10),
                    'total_harga' => $totalHarga,
                    'tipe_pengiriman' => $requestData['tipePengiriman'],
                    'tipe_pembayaran' => $requestData['tipePembayaran'],
                    'tujuan' => $requestData['tujuan'],
                    'catatan' => $requestData['catatan'],
                    'ongkir' => $requestData['ongkir'],
                    'payment_status' => 'pending',
                ]);


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

                Keranjang::destroy($requestData['keranjang_id']);

                $payload = [
                    'transaction_details' => [
                        'order_id' => $order->unique_string,
                        'gross_amount' => $order->total_harga,
                    ],
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email'      => $user->email,
                        'nomor_hp'   => $user->nomor_hp,
                    ],
                ];

                $snapToken = \Midtrans\Snap::getSnapToken($payload);
                $order->snap_token = $snapToken;
                $order->save();

                $this->response['snap_token'] = $snapToken;
            });

            return response()->json([
                'status'     => 'success',
                'snap_token' => $this->response,
            ]);
        } else {
            DB::transaction(function () use ($requestData) {
                $user = auth()->user();

                if ($requestData['ongkir'] >= 0) {
                    $totalHarga = $requestData['totalHarga'] + $requestData['ongkir'];
                } else {
                    $totalHarga = $requestData['totalHarga'];
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'unique_string' => Str::random(10),
                    'total_harga' => $totalHarga,
                    'tipe_pengiriman' => $requestData['tipePengiriman'],
                    'tipe_pembayaran' => $requestData['tipePembayaran'],
                    'tujuan' => $requestData['tujuan'],
                    'catatan' => $requestData['catatan'],
                    'ongkir' => $requestData['ongkir'],
                    'payment_status' => 'paid',
                ]);


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
                Keranjang::destroy($requestData['keranjang_id']);
            });
        }
    }

    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.serverKey');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        $order = Order::where('unique_string', $request->order_id)->first();

        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                $order->update(['payment_status' => 'paid', 'status' => 'Proses']);
            } elseif ($request->transaction_status == 'expire') {
                $order->update(['payment_status' => 'canceled']);
            }
        }
    }
    // END OF MIDTRANS INTEGRAGITON

    // START OF USER'S ORDER DATA 
    public function OrderPending()
    {
        $user = auth()->user();

        return Order::with(['orderBarangs', 'user'])
            ->where('user_id', $user->id)
            ->where('payment_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function OrderProses()
    {
        $user = auth()->user();

        return Order::with(['orderBarangs', 'user'])
            ->where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function OrderSelesai()
    {
        $user = auth()->user();

        return Order::with(['orderBarangs', 'user'])
            ->where('user_id', $user->id)
            ->where('status', 'Selesai')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function destroy(Request $request)
    {
        $order_id = $request->input('order_id');
        $orderData = Order::with('orderBarangs')
            ->where('id', $order_id)
            ->get();
        foreach ($orderData as $order) {
            foreach ($order->orderBarangs as $orderBarang) {
                $produk = Produk::where('id', $orderBarang["order_id"])->first();

                $produk->update([
                    'stok' => $produk->stok + $orderBarang->kuantitas
                ]);
            }
        }
        Order::destroy($order_id);

        return redirect()->back()->with('success', 'success hapus order');
    }
    // END OF USER'S ORDER DATA 

    // START OF PENJUAL'S ORDER DATA
    public function OrderPenjualMasuk()
    {
        $user = auth()->user();
        $kantin = $user->kantin;

        $orderBarangs = OrderBarang::with(['order', 'order.user'])
            ->whereHas('order', function ($query) {
                $query->where('payment_status', 'paid');
            })
            ->where('kantin_id', $kantin->id)
            ->get();

        $groupedOrders = $orderBarangs->groupBy('order_id');

        $sortedGroups = $groupedOrders->sortByDesc(function ($group) {
            return $group->max('created_at');
        });

        return $sortedGroups;
    }

    public function OrderPenjualSelesai()
    {
        $user = auth()->user();
        $kantin = $user->kantin;

        $orderBarangs = OrderBarang::with(['order', 'order.user'])
            ->whereHas('order', function ($query) {
                $query->where('status', 'Selesai');
            })
            ->where('kantin_id', $kantin->id)
            ->get();

        $groupedOrders = $orderBarangs->groupBy('order_id');

        $sortedGroups = $groupedOrders->sortByDesc(function ($group) {
            return $group->max('created_at');
        });

        return $sortedGroups;
    }

    public function UpdateStatusOrderProdukSelesai(Request $request)
    {
        $orderBarangIds = $request->input('OrderBarang_id');
    
        OrderBarang::whereIn('id', $orderBarangIds)->update(['status' => 'Selesai']);
    
        return response()->json(['message' => 'OrderBarang status updated to Selesai successfully']);
    }

    // END OF PENJUAL'S ORDER DATA
}
