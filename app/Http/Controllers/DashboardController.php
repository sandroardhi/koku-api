<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    // ADMIN
    public function fetchUangPenjual()
    {
        $uang_masuk = OrderBarang::where('status_uang', 'Sukses')->with('order', 'kantin', 'kantin.penjual')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }
    // ADMIN
    public function fetchUangPengantar()
    {
        $uang_masuk = Order::where('status_ongkir', 'Sukses')->with('pengantar')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }

    public function fetchUangSelesai()
    {
        $uang_selesai = OrderBarang::where('status_uang', 'Selesai')
            ->with('order', 'kantin', 'kantin.penjual')
            ->orderBy('updated_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->updated_at->format('Y-m-d H:i:s');
            })
            ->values();

        return response()->json([
            "uang_selesai" => $uang_selesai
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


    // END OF ADMIN

    // PENJUAL
    public function fetchUangMasukPenjual()
    {
        $user = auth()->user();
        $kantin = $user->kantin;
        $uang_masuk = OrderBarang::where('kantin_id', $kantin->id)->whereIn('status_uang', ['Sukses', 'Selesai'])->with('order')->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }
    // END OF PENJUAL

    // PENGANTAR
    public function fetchUangMasukPengantar()
    {
        $user = auth()->user();
        $uang_masuk = Order::where('pengantar_id', $user->id)->whereIn('status_ongkir', ['Sukses', 'Selesai'])->get();

        return response()->json([
            "uang_masuk" => $uang_masuk
        ]);
    }
    // END OF PENGANTAR
}
