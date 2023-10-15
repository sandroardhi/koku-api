<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        $user = User::whereEmail($request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Alamat email atau password salah',
            ]);
        }

        return [
            'access_token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => $user,
        ];
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email|string|email',
            'password' => 'required|confirmed'
        ]);

        if(!$request->tipe_user) {
            $tipe_user = "user";
        } else {
            $tipe_user = $request->tipe_user;
        };

        if(!$request->status) {
            $status = "active";
        } else {
            $status = $request->status;
        };

        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt(($request->password)),
            'tipe_user' => $tipe_user,
            'status' => $status
        ]);
    }
    public function logout(Request $request)
    {
        return $request->user()->currentAccessToken()->delete();
    }

    public function profile(Request $request)
    {
        // ini cuma ngembaliin info user yang lagi login
        return $request->user();
    }
}
