<?php

namespace App\Http\Controllers;

use App\Models\Kantin;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KeranjangController extends Controller
{

    public function addToCart(Request $request)
    {
        $productId = $request->input('productId');
        $user = auth()->user();
        $keranjang = $user->keranjang ?? $user->keranjang()->create();

        $kuantitas = $request->input('kuantitas', 1);

        if ($keranjang->produks()->where('produk_id', $productId)->exists()) {
            $keranjang->produks()->updateExistingPivot($productId, ['kuantitas' => $kuantitas]);
            return response()->json(['message' => 'berhasil memasukkan produk ke keranjang!']);
        } else {
            $keranjang->produks()->attach($productId, ['kuantitas' => $kuantitas]);
            return response()->json(['message' => 'berhasil menambah kuantitas!']);
        }
    }

    public function getCartData()
    {
        $user = auth()->user();
        $keranjang = $user->keranjang;

        if ($keranjang) {
            $produkData = $keranjang->produks()->withPivot('kuantitas')->get();

            return response()->json(['keranjang' => $keranjang, 'produkData' => $produkData ?? []]);
        }

        return response()->json(['message' => 'Cart is empty']);
    }


    public function deleteCartProduct(Request $request)
    {
        $produk_id = intval($request->input('produk_id'));
        $keranjang = auth()->user()->keranjang;

        if ($keranjang->produks()->where('produk_id', $produk_id)->exists()) {
            $keranjang->produks()->detach($produk_id);
            return response()->json(['message' => 'Product removed from cart.']);
        }

        return response()->json(['error' => 'Product not found in the cart.'], 400);
    }

    public function updateKuantitas(Request $request)
    {
        $produk_id = $request->input('produk_id');
        $produk = Produk::findOrFail($produk_id);
        $keranjang = auth()->user()->keranjang;
        $action = $request->input('action');

        if ($keranjang->produks()->where('produk_id', $produk->id)->exists()) {
            $currentKuantitas = $keranjang->produks()->where('produk_id', $produk->id)->first()->pivot->kuantitas;

            $newKuantitas = ($action === 'increment') ? $currentKuantitas + 1 : max($currentKuantitas - 1, 0);

            if ($newKuantitas > 0) {
                if ($newKuantitas <= $produk->stok) {
                    $keranjang->produks()->updateExistingPivot($produk->id, ['kuantitas' => DB::raw($newKuantitas)]);
                    return response()->json(['message' => 'Kuantitas updated successfully']);
                } else {
                    return response()->json(['error' => 'Insufficient stock for the operation.'], 400);
                }
            } else {
                // Remove the record from the pivot table
                $keranjang->produks()->detach($produk->id);
                return response()->json(['message' => 'Product removed from cart.']);
            }
        }
        return response()->json(['error' => 'Invalid product in the cart.'], 400);
    }
}
