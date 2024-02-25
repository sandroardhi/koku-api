<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderBarang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    // ADMIN
    public function fetchUangPenjual()
    {
        $uang_masuk = OrderBarang::where('status_uang', 'Sukses')->with('order', 'kantin', 'kantin.penjual')->orderBy('updated_at', 'desc')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }

    public function fetchUangPengantar()
    {
        $uang_masuk = Order::where('status_ongkir', 'Sukses')->with('pengantar')->orderBy('updated_at', 'desc')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }

    public function fetchUangRefund()
    {
        $uang_masuk = OrderBarang::where('status_uang', 'Refund')->with('order', 'order.user')->orderBy('updated_at', 'desc')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }

    public function fetchUangSelesai()
    {
        $uang_selesai = OrderBarang::where('status_uang', 'Selesai')
            ->whereNot('status', 'Gagal Dibuat')
            ->with('order', 'kantin', 'kantin.penjual')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->updated_at->format('Y-m-d H:i:s');
            })
            ->values();

        $uang_refunded = OrderBarang::where('status_uang', 'Refunded')
            ->with('order', 'order.user')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->updated_at->format('Y-m-d H:i:s');
            })
            ->values();

        $uang_ongkir_selesai = Order::where('status_ongkir', 'Selesai')
            ->with('pengantar')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->updated_at->format('Y-m-d H:i:s');
            })
            ->values();


        return response()->json([
            "uang_selesai" => $uang_selesai,
            "uang_refunded" => $uang_refunded,
            "uang_ongkir_selesai" => $uang_ongkir_selesai
        ]);
    }


    public function bayarPenjual(Request $request)
    {
        $validatedData = $request->validate([
            'kantin_id' => 'required',
            'orderbarang_id' => 'required|array',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif'
        ]);

        $orderbarangIds = is_array($validatedData['orderbarang_id']) ?
            $validatedData['orderbarang_id'] :
            explode(',', $validatedData['orderbarang_id']);

        foreach ($orderbarangIds as $orderbarangId) {
            $orderbarang = OrderBarang::findOrFail($orderbarangId);

            $orderbarang->status_uang = 'Selesai';
            $orderbarang->lampiran =  $request->file('foto')->store('foto_lampiran', 'public');

            $orderbarang->save();
        }

        return response()->json([
            'message' => 'Sukses membayar penjual'
        ]);
    }



    public function bayarPengantar(Request $request)
    {
        $validatedData = $request->validate([
            'pengantar_id' => 'required',
            'order_id' => 'required|array',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif'
        ]);

        $orderIds = is_array($validatedData['order_id']) ?
            $validatedData['order_id'] :
            explode(',', $validatedData['order_id']);

        foreach ($orderIds as $orderId) {
            $order = Order::findOrFail($orderId);

            $order->status_ongkir = 'Selesai';
            $order->lampiran =  $request->file('foto')->store('foto_lampiran', 'public');

            $order->save();
        }

        return response()->json([
            'message' => 'Sukses membayar penjual'
        ]);
    }


    public function bayarRefund(Request $request)
    {
        $validatedData = $request->validate([
            'orderbarang_id' => 'required|array',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif'
        ]);

        $orderbarangIds = is_array($validatedData['orderbarang_id']) ?
            $validatedData['orderbarang_id'] :
            explode(',', $validatedData['orderbarang_id']);

        foreach ($orderbarangIds as $orderbarangId) {
            $orderbarang = OrderBarang::findOrFail($orderbarangId);

            $orderbarang->status_uang = 'Refunded';
            $orderbarang->lampiran =  $request->file('foto')->store('foto_lampiran', 'public');

            $orderbarang->save();
        }

        return response()->json([
            'message' => 'Sukses membayar penjual'
        ]);
    }


    public function dashboardAdmin()
    {
        $today = Carbon::today();

        $bayarPenjual = OrderBarang::whereDate('created_at', $today)
            ->where('status_uang', 'Sukses')
            ->sum('harga');

        $bayarPengantar = Order::whereDate('created_at', $today)
            ->where('status_ongkir', 'Sukses')
            ->sum('ongkir');

        $bayarRefund = OrderBarang::whereDate('created_at', $today)
            ->where('status_uang', 'Refund')
            ->sum('harga');

        return [
            'bayarPenjual' => $bayarPenjual,
            'bayarPengantar' => $bayarPengantar,
            'bayarRefund' => $bayarRefund
        ];
    }

    // END OF ADMIN

    // PEMBELI
    public function fetchUangRefundPembeli()
    {
        $user = auth()->user();
        $uang_masuk = Order::where('user_id', $user->id)
            ->whereHas('orderBarangs', function ($query) {
                $query->where('status_uang', 'Refund')
                    ->orWhere('status_uang', 'Refunded');
            })
            ->with(['orderBarangs' => function ($query) {
                $query->where('status_uang', 'Refund')
                    ->orWhere('status_uang', 'Refunded');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }

    // END OF PEMBELI

    // PENJUAL
    public function fetchUangMasukPenjual()
    {
        $user = auth()->user();
        $kantin = $user->kantin;
        $uang_masuk = OrderBarang::where('kantin_id', $kantin->id)->whereIn('status_uang', ['Sukses', 'Selesai'])->with('order', 'order.user')->orderBy('updated_at', 'desc')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }

    public function dashboardPenjual()
    {
        $user = auth()->user();
        $kantin = $user->kantin;
        $today = Carbon::today();

        $uangMasuk = OrderBarang::whereDate('created_at', $today)->where('kantin_id', $kantin->id)->whereIn('status_uang', ['Sukses', 'Selesai'])->sum('harga');

        $produkTerjual = OrderBarang::whereDate('created_at', $today)
            ->where('kantin_id', $kantin->id)
            ->where('status', 'Selesai')
            ->count();

        $orderMasuk = OrderBarang::whereDate('created_at', $today)
            ->where('kantin_id', $kantin->id)
            ->select(DB::raw('count(distinct order_id) as unique_order_ids'))
            ->first()
            ->unique_order_ids;

        return [
            'uangMasuk' => $uangMasuk,
            'produkTerjual' => $produkTerjual,
            'orderMasuk' => $orderMasuk
        ];
    }

    // END OF PENJUAL

    // PENGANTAR
    public function fetchUangMasukPengantar()
    {
        $user = auth()->user();
        $uang_masuk = Order::where('pengantar_id', $user->id)->whereIn('status_ongkir', ['Sukses', 'Selesai'])->orderBy('updated_at', 'desc')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }

    public function dashboardPengantar()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $uangMasuk = Order::whereDate('created_at', $today)->where('pengantar_id', $user->id)->where('status', 'Selesai')->sum('ongkir');

        $mengantarOrder = Order::whereDate('created_at', $today)->where('pengantar_id', $user->id)->count();

        $result = Order::whereDate('created_at', $today)
            ->where('pengantar_id', $user->id)
            ->selectSub(function ($query) {
                $query->from('order_barang')
                    ->select(DB::raw('count(*)'))
                    ->whereColumn('order_id', 'orders.id')
                    ->where('status', 'Selesai'); // Additional condition for status
            }, 'order_barang_count')
            ->first();

        $mengantarProduk = $result ? $result->order_barang_count : 0;

        return [
            'uangMasuk' => $uangMasuk,
            'mengantarOrder' => $mengantarOrder,
            'mengantarProduk' => $mengantarProduk
        ];
    }
    // END OF PENGANTAR
}
