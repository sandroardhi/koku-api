<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ProdukController extends Controller
{
    public function show_produk($id)
    {
        return Produk::where('kantin_id', $id)->get();
    }


    public function store(Request $request, $kantinId)
    {
        $user = auth()->user(); // Assuming you have user authentication

        // Assuming products are sent as an array with a key named 'products'
        $productsData = $request->input('products');

        // Validate each product and store them
        foreach ($productsData as $index => $productData) {
            $request->validate([
                "products.{$index}.nama" => 'required|string|max:255',
                "products.{$index}.harga" => 'required|numeric|min:1',
                "products.{$index}.stok" => 'required|numeric|min:1',
                "products.{$index}.deskripsi" => 'required',
                "products.{$index}.kategori_id" => 'required',
                "products.{$index}.foto" => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5000',
            ]);

            $file = $request->file("products.{$index}.foto");
            $uniqueIdentifier = Str::uuid()->toString();
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = "{$uniqueIdentifier}.{$fileExtension}";
            $fotoPath = $file->storeAs('foto_produk', $fileName, 'public');


            $product = new Produk([
                'nama' => $productData['nama'],
                'harga' => $productData['harga'],
                'stok' => $productData['stok'],
                'deskripsi' => $productData['deskripsi'],
                'foto' => $fotoPath,
                'penjual_id' => $user->id,
                'kantin_id' => $kantinId,
                'kategori_id' => $productData['kategori_id']
            ]);

            $product->save();
        }

        return response()->json(['message' => 'Produk berhasil dibuat!'], 201);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'nama' => 'required|string',
            'harga' => 'required',
            'stok' => 'required',
            'kategori_id' => 'required',
            'deskripsi' => 'required',
        ];

        if ($request->hasFile('foto')) {
            $rules['foto'] = 'image|mimes:jpeg,png,jpg,gif|max:5000';
        } else {
            $rules['foto'] = 'string'; // iki lek ga atek update foto
        }
        $this->validate($request, $rules);

        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk not found'], 404);
        }

        $produk->nama = $request->input('nama');
        $produk->harga = $request->input('harga');
        $produk->stok = $request->input('stok');
        $produk->deskripsi = $request->input('deskripsi');
        $produk->kategori_id = $request->input('kategori_id');

        if ($request->hasFile('foto')) {
            // Delete existing file if it exists
            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }

            // Store the new file
            $fotoPath = $request->file('foto')->store('foto_produk', 'public');
            $produk->foto = $fotoPath;
        }

        $produk->save();

        return response()->json(['message' => 'Produk berhasil diupdate!']);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ada?!'], 404);
        }

        // Delete the associated image
        if ($produk->foto) {
            Storage::disk('public')->delete($produk->foto);
        }

        $produk->delete();

        return response()->json(['message' => 'Produk berhasil didelete']);
    }
}
