<?php

namespace App\Http\Controllers;

use App\Models\Tujuan;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->orWhere('name', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Alamat email atau password salah',
            ]);
        }
        $role = $user->roles->pluck('name')->first();

        return [
            'access_token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => $user,
            'role' => $role,
        ];
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email|string|email',
            'password' => 'required|confirmed'
        ]);

        $user =  User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt(($request->password)),
            'status' => $request->status
        ]);

        $user->assignRole('user');

        return $user;
    }
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Berhasil logout']);
    }

    public function profile(Request $request)
    {
        // ini cuma ngembaliin info user yang lagi login
        return $request->user();
    }

    public function tujuan()
    {
        $user = auth()->user();
        return  $user->tujuans;
    }

    public function create_tujuan(Request $request)
    {
        Log::info("create tujuan ini");
        Log::info($request->tujuan);
        $user = auth()->user();
        $request->validate([
            'tujuan' => 'required|string|max:255',
        ]);
        Tujuan::create([
            'tujuan' => $request->tujuan,
            'user_id' => $user->id
        ]);

        return response()->json(['message' => 'Berhasil buat tujuan']);
    }
}
