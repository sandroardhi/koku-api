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
        if ($user->status == 'pending') {
            return response()->json([
                'message' => 'Akun belum diaktivasi, tunggu admin untuk aktivasi akun ini'
            ], 403);
        } elseif ($user->status == 'suspended') {
            return response()->json([
                'message' => 'Akun ter-suspend, anda tidak dapat login dengan akun ini'
            ], 403);
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


    public function registerPenjual(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email|string|email',
            'password' => 'required|confirmed',
            'no_rek' => 'required',
            'selectedChannel' => 'required'
        ]);

        $user =  User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt(($request->password)),
            'status' => 'pending',
            'no_rek' => $request->no_rek,
            'channel' => $request->selectedChannel
        ]);

        $user->assignRole('penjual');

        return $user;
    }

    public function registerPengantar(Request $request)
    {
        Log::info($request->all());
        $validatedData = $request->validate([
            'user_id' => 'required',
            'channel' => 'required',
            'no_rek' => 'required'
        ]);
        $user = User::where('id', $request->user_id)->first();

        $user->channel = $request->channel;
        $user->no_rek = $request->no_rek;
        $user->status = 'pending';
        $user->save();

        $user->syncRoles(['pengantar']);


        return response()->json([
            'message' => 'Berhasil meng-update rekening'
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Berhasil logout']);
    }

    public function getUser()
    {
        $user = auth()->user();
        return [
            'user' => $user,
        ];
    }

    public function tujuan()
    {
        $user = auth()->user();
        return  $user->tujuans;
    }

    public function update_rekening(Request $request)
    {
        Log::info($request->all());
        $validatedData = $request->validate([
            'channel' => 'required',
            'no_rek' => 'required'
        ]);
        $user = auth()->user();

        // $user->update([
        //     'channel' => $validatedData['channel'],
        //     'no_rek' => $validatedData['no_rek']
        // ]);

        $user->channel = $request->channel;
        $user->no_rek = $request->no_rek;
        $user->save();

        return response()->json([
            'message' => 'Berhasil meng-update rekening'
        ]);
    }

    public function create_tujuan(Request $request)
    {
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
