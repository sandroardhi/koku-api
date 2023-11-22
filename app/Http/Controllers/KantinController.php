<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kantin;

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
            'nama_kantin' => 'required|string|max:255|unique:kantin,nama_kantin',
            'foto_kantin' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
            'deskripsi' => 'nullable|string|max:750'
        ]);
        if($request->foto_kantin){
            $foto_path = $request->file('foto_kantin')->store('foto_kantin', 'public');
        }
        if($request->image){
            return Kantin::create([
                ...$request->validated(),
                'image' => $foto_path,
                'user_id' => $request->user()->id,
            ]);
        }
        else {
            return Kantin::create([
                ...$request->validated(),
                'user_id' => $request->user()->id,
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
