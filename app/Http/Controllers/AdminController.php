<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function users()
    {
        // Retrieve users with roles
        $usersWithRoles = User::with('roles')->get();

        // Prepare the data for JSON response
        $data = $usersWithRoles->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'role' => $user->roles->pluck('name')->first(), 
                'role_id' => $user->roles->pluck('id')->first(), 
            ];
        });

        // Return JSON response
        return response()->json($data);
 
    }
    public function update_role(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required',
            'status' => 'required|in:active,pending,suspended',
        ]);

        $user = User::findOrFail($id);
    
        // Assuming you have a role_id in the request payload
        $roleId = $request->input('role_id');
        $role = Role::findOrFail($roleId);
    
        // Sync the user's roles
        $user->syncRoles([$role->name]);
    
        $user->update($request->only(['status']));

        return response()->json(['message' => 'User role and status updated successfully']);
    }
}
