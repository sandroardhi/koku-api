<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function users()
    {
        return User::orderBy("name")->get();
    }
    public function update_role(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required',
            'status' => 'required|in:active,pending,suspended',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['role_id', 'status']));

        return response()->json(['message' => 'User role and status updated successfully']);
    }
}
