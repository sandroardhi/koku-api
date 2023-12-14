<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kantin;
use Illuminate\Support\Facades\Storage;

class KantinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Kantin::all();
    }

    // gae display kantin milik penjual
    public function show_profile_kantin($id)
    {
        return Kantin::where('penjual_id', $id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kantin,nama',
            'foto' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
            'deskripsi' => 'nullable|string|max:750'
        ]);

        if ($request->hasFile('foto')) {
            $foto_path = $request->file('foto')->store('foto_kantin', 'public');
        }
        if ($request->foto) {
            return Kantin::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'foto' => $foto_path,
                'penjual_id' => $request->user()->id,
            ]);
        } else {
            return Kantin::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'foto' => 'default.jpg',
                'penjual_id' => $request->user()->id,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // 
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $kantin)
    {
        $rules = [
            'nama' => 'required|string',
            'deskripsi' => 'required|string',
        ];

        if ($request->hasFile('foto')) {
            $rules['foto'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['foto'] = 'string'; // Adjust as needed
        }
        $this->validate($request, $rules);

        $kantin = Kantin::find($kantin);

        if (!$kantin) {
            return response()->json(['message' => 'Kantin not found'], 404);
        }

        $kantin->nama = $request->input('nama');
        $kantin->deskripsi = $request->input('deskripsi');

        if ($request->hasFile('foto')) {
            // Delete existing file if it exists
            if ($kantin->foto) {
                Storage::disk('public')->delete($kantin->foto);
            }

            // Store the new file
            $fotoPath = $request->file('foto')->store('foto_kantin', 'public');
            $kantin->foto = 'foto_kantin/' . basename($fotoPath);
            
        }

        $kantin->save();

        return response()->json(['message' => 'Kantin berhasil diupdate!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
