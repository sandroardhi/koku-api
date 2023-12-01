<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function show_produk($id) {
        return Produk::where('kantin_id', $id)->get();
    }
}
