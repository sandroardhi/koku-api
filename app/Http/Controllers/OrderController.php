<?php

namespace App\Http\Controllers;

use App\Jobs\BuatOrderJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Produk;
use App\Models\Keranjang;
use App\Models\Order;
use App\Models\OrderBarang;
use App\Models\User;
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
        // check if tipePengiriman e 'Antar'
        if ($requestData['tipePengiriman'] === 'Antar') {
            $pengantars = User::role('pengantar')
                ->where('pengantarIsAvailable', 'active')
                ->get();
            // lek pengiriman e antar, check onok pengantar active gak
            // if ($pengantars == 'asodkjasokdjsadkoasj') {
            if ($pengantars->count() > 0) {
                // check lek tipe_pembayaran e 'Online'
                if ($requestData['tipePembayaran'] == 'Online') {
                    $requestData['pengantar'] = null;

                    if ($requestData['ongkir'] >= 0) {
                        $totalHarga = $requestData['totalHarga'] + $requestData['ongkir'];
                    } else {
                        $totalHarga = $requestData['totalHarga'];
                    }

                    $requestData['totalHarga'] = $totalHarga;
                    $requestData['unique_string'] = Str::random(10);

                    DB::transaction(function () use ($requestData) {
                        $user = auth()->user();

                        $payload = [
                            'transaction_details' => [
                                'order_id' => $requestData['unique_string'],
                                'gross_amount' => $requestData['totalHarga'],
                            ],
                            'customer_details' => [
                                'first_name' => $user->name,
                                'email'      => $user->email,
                                'nomor_hp'   => $user->nomor_hp,
                            ],
                        ];

                        $snapToken = \Midtrans\Snap::getSnapToken($payload);
                        $requestData['snapToken'] = $snapToken;
                        $requestData['payment_status'] = 'pending';


                        BuatOrderJob::dispatch($requestData);

                        $this->response['snap_token'] = $snapToken;
                    });

                    return response()->json([
                        'status'     => 'success',
                        'snap_token' => $this->response,
                        'message' => 'Order berhasil dibuat'
                    ]);
                }
                // iki lek tipe_pembayaran e 'Cash' 
                else {
                    $requestData['pengantar'] = $pengantars->first();
                    DB::transaction(function () use ($requestData) {
                        $requestData['unique_string'] = Str::random(10);
                        $requestData['payment_status'] = 'paid';
                        BuatOrderJob::dispatch($requestData);

                        return response()->json([
                            'status'     => 'success',
                            'message' => 'Order berhasil dibuat'
                        ]);
                    });
                }
            }
            // lek gaonok pengantar e return 404
            else {
                return response()->json([
                    'message' => 'Tidak ada pengantar yang available saat ini..',
                    'pengantars' =>  $pengantars
                ], 404);
            }
        }
        // lek tipe_pengiriman e gak 'Antar'
        else {
            $requestData['pengantar'] = null;
            if ($requestData['tipePembayaran'] == 'Online') {

                if ($requestData['ongkir'] >= 0) {
                    $totalHarga = $requestData['totalHarga'] + $requestData['ongkir'];
                } else {
                    $totalHarga = $requestData['totalHarga'];
                }
                $requestData['totalHarga'] = $totalHarga;
                $requestData['unique_string'] = Str::random(10);

                DB::transaction(function () use ($requestData) {
                    $user = auth()->user();

                    $payload = [
                        'transaction_details' => [
                            'order_id' => $requestData['unique_string'],
                            'gross_amount' => $requestData['totalHarga'],
                        ],
                        'customer_details' => [
                            'first_name' => $user->name,
                            'email'      => $user->email,
                            'nomor_hp'   => $user->nomor_hp,
                        ],
                    ];

                    $snapToken = \Midtrans\Snap::getSnapToken($payload);
                    $requestData['snapToken'] = $snapToken;
                    $requestData['payment_status'] = 'pending';


                    BuatOrderJob::dispatch($requestData);

                    $this->response['snap_token'] = $snapToken;
                });

                return response()->json([
                    'status'     => 'success',
                    'snap_token' => $this->response,
                    'message' => 'Order berhasil dibuat'
                ]);
            } else {
                DB::transaction(function () use ($requestData) {
                    $requestData['unique_string'] = Str::random(10);

                    $requestData['payment_status'] = 'paid';
                    BuatOrderJob::dispatch($requestData);

                    return response()->json([
                        'status'     => 'success',
                        'message' => 'Order berhasil dibuat'
                    ]);
                });
            }
        }
    }

    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.serverKey');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        $order = Order::where('unique_string', $request->order_id)->first();

        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                $order->update(['payment_status' => 'paid']);
            } elseif ($request->transaction_status == 'expire') {
                $order->update(['payment_status' => 'canceled']);
            }
        }
    }

    // END OF MIDTRANS INTEGRAGITON

    // START OF USER'S ORDER DATA 
    public function checkPengantar(Request $request)
    {
        $requestData = $request->json()->all();

        $order = $requestData['order'];
        if ($order['tipe_pengiriman'] == 'Antar') {
            $pengantars = User::role('pengantar')
                ->where('pengantarIsAvailable', 'active')
                ->get();

            if ($pengantars->count() > 0) {
                return response()->json([
                    'message' => 'Pengantar ada',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Pengantar tidak ditemukan',
                ], 404);
            }
        }
    }

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
            ->whereNotIn('status', ['Selesai', 'Canceled'])
            ->orWhereIn('status', ['Menunggu Konfirmasi', 'Proses', 'Dikirim', 'Konfirmasi Pembeli'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function OrderSelesai()
    {
        $user = auth()->user();

        return Order::with(['orderBarangs', 'user'])
            ->where('user_id', $user->id)
            ->WhereIn('status', ['Selesai', 'Canceled'])
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

        return response()->json(['message' => 'Order destroyed']);
    }

    public function UserUpdateOrderSelesai(Request $request)
    {
        $order_id = $request->input('order_id');

        $order = Order::where('id', $order_id)->first();

        $order->status = 'Selesai';
        $order->save();

        return response()->json(['message' => 'Order status updated to Selesai successfully']);
    }
    // END OF USER'S ORDER DATA 

    // START OF PENJUAL'S ORDER DATA
    public function OrderPenjualMasuk()
    {
        $user = auth()->user();
        $kantin = $user->kantin;

        $orderBarangs = OrderBarang::with(['order', 'order.user'])
            ->whereHas('order', function ($query) {
                $query->where('status', 'Menunggu Konfirmasi')
                    ->orWhere('status', 'Proses')
                    ->orWhere('status', 'Konfirmasi Pembeli')
                    ->orWhere('status', 'Dikirim');
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

    public function OrderPenjualCancel()
    {
        $user = auth()->user();
        $kantin = $user->kantin;

        $orderBarangs = OrderBarang::with(['order', 'order.user'])
            ->whereHas('order', function ($query) {
                $query->where('status', 'Canceled');
            })
            ->orWhere('status', 'Gagal Dibuat')
            ->where('kantin_id', $kantin->id)
            ->get();

        $groupedOrders = $orderBarangs->groupBy('order_id');

        $sortedGroups = $groupedOrders->sortByDesc(function ($group) {
            return $group->max('created_at');
        });

        return $sortedGroups;
    }

    public function orderMasukCount()
    {
        $user = auth()->user();

        return Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['Selesai', 'Canceled'])
            ->orWhereIn('status', ['Menunggu Konfirmasi', 'Proses', 'Dikirim', 'Konfirmasi Pembeli'])
            ->count();
    }

    public function UpdateStatusOrderProdukDibuat(Request $request)
    {
        $orderBarangIds = $request->input('OrderBarang_id');

        foreach ($orderBarangIds as $orderBarangId) {
            $orderBarang = OrderBarang::find($orderBarangId);
            if ($orderBarang) {
                $orderBarang->status = 'Dibuat';
                $orderBarang->save();
            }
        }

        return response()->json(['message' => 'OrderBarang status updated to Dibuat successfully']);
    }

    public function UpdateStatusOrderProdukSelesai(Request $request)
    {
        $orderBarangIds = $request->input('OrderBarang_id');

        foreach ($orderBarangIds as $orderBarangId) {
            $orderBarang = OrderBarang::find($orderBarangId);
            if ($orderBarang) {
                $orderBarang->status = 'Selesai';
                $orderBarang->save();
            }
        }

        return response()->json(['message' => 'OrderBarang status updated to Selesai successfully']);
    }

    // END OF PENJUAL'S ORDER DATA

    // START OF PENGANTAR ORDER DATA
    public function orderPengantarCount()
    {
        $user = auth()->user();

        return Order::where('pengantar_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['Selesai', 'Canceled'])
            ->orWhereIn('status', ['Menunggu Konfirmasi', 'Proses', 'Dikirim', 'Konfirmasi Pembeli'])
            ->count();
    }
    // END OF PENGANTAR ORDER DATA
}
