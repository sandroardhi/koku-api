<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles  = Role::orderBy('id', 'DESC')->get();

        return response($roles, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'permission' => 'required'
            ]);

            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

            $role->syncPermissions($request->permission);

            return response('Success', 201);
        } catch (Exception $e) {
            return response($e->getMessage(), 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permission", "role_has_permission.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();

        $data['roles'] = $role;
        $data['rolePermissions'] = $rolePermissions;

        return response($data, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'permission' => 'required'
        ]);

        $role = Role::find($id);
        $role->name = $request->name;
        $role->save();
        
        $role->syncPermissions($request->permission);
        
        return response('Success', 200);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);
        
        if ($role) {
            $role->delete();

            return $this->sendResponse(null, 'Success', 200);
        }
        return response('Error delete', 404);
    }

        /**
     * Iki digae ngefetch all permission
     */
    public function fetch_permission()
    {
        $permissions = Permission::get();
        

        return response($permissions, 200);
    }

    /**
     * Iki kyk digae edit page ngunu se
     */
    public function fetch_role_edit_data(string $id)
    {
        $role = Role::find($id);


        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck("role_has_permissions.permission_id", "role_has_permissions.permission_id")
            ->all();

        $data['role'] = $role;
        $data['rolePermissions'] = $rolePermissions;

        return response($data, 200);
    }
}
