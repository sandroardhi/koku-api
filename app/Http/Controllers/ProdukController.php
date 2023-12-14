<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
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
                "products.{$index}.kuantitas" => 'required|numeric|min:1',
                "products.{$index}.foto" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle file 
            $file = $request->file("products.{$index}.foto");
            $fileName = "{$index}_{$file->getClientOriginalName()}"; // Adjust the file name as needed
            $filePath = $file->storeAs('produk_images', $fileName, 'public');

            $product = new Produk([
                'nama' => $productData['nama'],
                'harga' => $productData['harga'],
                'kuantitas' => $productData['kuantitas'],
                'foto' => $filePath,
                'penjual_id' => $user->id,
                'kantin_id' => $kantinId,
                'kategori_id' => '1'
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
            'kuantitas' => 'required',
        ];

        if ($request->hasFile('foto')) {
            $rules['foto'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['foto'] = 'string'; // Adjust as needed
        }
        $this->validate($request, $rules);

        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk not found'], 404);
        }

        $produk->nama = $request->input('nama');
        $produk->harga = $request->input('harga');
        $produk->kuantitas = $request->input('kuantitas');

        if ($request->hasFile('foto')) {
            // Delete existing file if it exists
            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }

            // Store the new file
            $fotoPath = $request->file('foto')->store('foto_produk', 'public');
            $produk->foto = 'foto_produk/' . basename($fotoPath);
        }

        $produk->save();

        return response()->json(['message' => 'Produk berhasil diupdate!']);
    }
}
