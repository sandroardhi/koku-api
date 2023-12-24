<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class KategoriController extends Controller
{
    public function index()
    {
        return Kategori::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kantin,nama',
            'foto' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
        ]);

        if ($request->hasFile('foto')) {
            $foto_path = $request->file('foto')->store('foto_kategori', 'public');
        }
        if ($request->foto) {
            return Kategori::create([
                'nama' => $request->nama,
                'foto' => $foto_path,
            ]);
        } else {
            return Kategori::create([
                'nama' => $request->nama,
                'foto' => 'default.jpg',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        $rules = [
            'nama' => 'required|string',
        ];

        if ($request->hasFile('foto')) {
            $rules['foto'] = 'image|mimes:jpeg,png,jpg,gif|max:5000';
        } else {
            $rules['foto'] = 'string'; // iki lek ga atek update foto
        }
        $this->validate($request, $rules);

        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json(['message' => 'Kategori not found'], 404);
        }

        $kategori->nama = $request->input('nama');

        if ($request->hasFile('foto')) {
            // Delete existing file if it exists
            if ($kategori->foto) {
                Storage::disk('public')->delete($kategori->foto);
            }

            // Store the new file
            $fotoPath = $request->file('foto')->store('foto_kategori', 'public');
            $kategori->foto = 'foto_kategori/' . basename($fotoPath);
        }

        $kategori->save();

        return response()->json(['message' => 'Kategori berhasil diupdate!']);
    }
    public function destroy($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ada?!'], 404);
        }

        // Delete the associated image
        if ($kategori->foto) {
            Storage::disk('public')->delete($kategori->foto);
        }

        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil didelete']);
    }
}
