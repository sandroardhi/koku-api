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

        if ($requestData['ongkir'] >= 0) {
            $totalHarga = $requestData['totalHarga'] + $requestData['ongkir'];
        } else {
            $totalHarga = $requestData['totalHarga'];
        }

        $uniqueString = Str::random(10);

        $requestData['totalHarga'] = $totalHarga;
        $requestData['unique_string'] = $uniqueString;

        Log::info($requestData);

        if ($requestData['tipePembayaran'] == 'Online') {
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
                $requestData['payment_status'] = 'paid';
                BuatOrderJob::dispatch($requestData);

                return response()->json([
                    'status'     => 'success',
                    'message' => 'Order berhasil dibuat'
                ]);
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
                $order->update(['payment_status' => 'paid', 'status' => 'Menunggu Konfirmasi']);
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
            ->orWhere('status', 'Proses')
            ->orWhere('status', 'Dikirim')
            ->orWhere('status', 'Konfirmasi Pembeli')
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

        return response()->json(['message' => 'OrderBarang status updated to Selesai successfully']);
    }

    public function UserUpdateOrderSelesai(Request $request)
    {
        $order_id = $request->input('order_id');

        $order = Order::where('id', $order_id)->first();

        $order->status = 'Selesai';
        $order->save();

        return response()->json(['message' => 'OrderBarang status updated to Dibuat successfully']);
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
                    ->orWhere('status', 'Konfirmasi Pembeli');
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
            ->where('kantin_id', $kantin->id)
            ->get();

        $groupedOrders = $orderBarangs->groupBy('order_id');

        $sortedGroups = $groupedOrders->sortByDesc(function ($group) {
            return $group->max('created_at');
        });

        return $sortedGroups;
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
}
