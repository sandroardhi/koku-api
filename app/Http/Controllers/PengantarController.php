<?php

namespace App\Http\Controllers;

use App\Models\OrderBarang;
use Illuminate\Http\Request;

class PengantarController extends Controller
{
    public function togglePengantarActive()
    {
        $user = auth()->user();
        $user->update(['pengantarIsAvailable' => 'active']);

        return response()->json(['message' => 'Pengantar sekarang active']);
    }

    public function togglePengantarNonActive()
    {
        $user = auth()->user();
        $user->update(['pengantarIsAvailable' => 'nonactive']);

        return response()->json(['message' => 'Pengantar sekarang non-active']);
    }

    public function OrderPengantarMasuk()
    {
        $pengantar = auth()->user();

        if ($pengantar->hasRole('pengantar')) {
            $assignedOrders = $pengantar->assignedOrders()
                ->with('OrderBarangs', 'user')
                ->whereIn('status', ['Menunggu Konfirmasi', 'Proses', 'Dikirim'])
                ->get();

            return response()->json([
                'assigned_orders' => $assignedOrders,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized access. Only pengantars can access this endpoint.',
            ], 403);
        }
    }

    public function OrderPengantarSelesai()
    {
        $pengantar = auth()->user();

        if ($pengantar->hasRole('pengantar')) {
            $assignedOrders = $pengantar->assignedOrders()
                ->whereIn('status', ['Selesai'])
                ->get();

            return response()->json([
                'assigned_orders' => $assignedOrders,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized access. Only pengantars can access this endpoint.',
            ], 403);
        }
    }

    public function OrderPengantarCancel()
    {
        $pengantar = auth()->user();

        if ($pengantar->hasRole('pengantar')) {
            $assignedOrders = $pengantar->assignedOrders()
                ->whereIn('status', ['Canceled'])
                ->get();

            return response()->json([
                'assigned_orders' => $assignedOrders,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized access. Only pengantars can access this endpoint.',
            ], 403);
        }
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
}
