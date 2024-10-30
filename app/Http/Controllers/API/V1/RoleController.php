<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RoleResource;
use App\Models\V1\Role;
use App\Models\V1\RoleUser;
use App\Models\V1\Permissionable;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected function roles_select()
    {
        $roles = Role::select('*');

        return $roles;
    }

    protected function roles_filter(Request $request, $roles)
    {
        $name_filter = $request->get('name_filter');

        $sort_field = $request->get('sort_field');
        $sort_type = $request->get('sort_type');

        if ($name_filter) {
            $roles->nameFilter($name_filter);
        }

        if ($sort_field && $sort_type) {
            $roles = $roles->orderBy($sort_field, $sort_type);
        }

        return $roles;
    }

    public function index(Request $request)
    {
        $roles = $this->roles_select();
        $roles = $this->roles_filter($request, $roles);

        $per_page = $request->get('per_page');

        if ($per_page) {
            $roles = $roles->paginate($per_page);
        } else {
            $roles = $roles->paginate(100);
        }

        return RoleResource::collection($roles);
    }

    public function show($id)
    {
        $permissions = auth()->user()->permissions;
        if(!$permissions->where('name', 'users_managment')->count()){
            return response('', 403);
        }

        $role = Role::find($id);

        return new RoleResource($role);
    }

    public function store(Request $request)
    {
        $permissions = auth()->user()->permissions;
        if(!$permissions->where('name', 'users_managment')->count()){
            return response('', 403);
        }

        $role = Role::create(([
            'name' => $request->name,
        ]));

        foreach($request->newPermissions as $newPermission)
        {
            Permissionable::create(([
                'permissionable_id' => $role->id,
                'permission_id' => $newPermission,
                'permissionable_type' => 'App\Models\V1\Role'
            ]));
        }

        return new RoleResource($role->refresh());
    }

    public function update($id, Request $request)
    {
        $permissions = auth()->user()->permissions;
        if(!$permissions->where('name', 'users_managment')->count()){
            return response('', 403);
        }

        $role = Role::find($id);
        $role->update($request->all());

        foreach($request->newPermissions as $newPermission)
        {
            Permissionable::create(([
                'permissionable_id' => $role->id,
                'permission_id' => $newPermission,
                'permissionable_type' => 'App\Models\V1\Role'
            ]));
        }

        foreach($request->deletedPermissions as $deletedPermission)
        {
            Permissionable::where('permission_id', $deletedPermission)
            ->where('permissionable_id', $role->id)
            ->where('permissionable_type', 'App\Models\V1\Role')
            ->delete();
        }

        return new RoleResource($role->refresh());
    }

    public function destroy($id)
    {
        $permissions = auth()->user()->permissions;
        if(!$permissions->where('name', 'users_managment')->count()){
            return response('', 403);
        }

        $role = Role::find($id);

        Permissionable::where('permissionable_id', $role->id)
        ->where('permissionable_type', 'App\Models\V1\Role')
        ->delete();

        RoleUser::where('role_id', $role->id)
        ->delete();

        $role->delete();
    }
}
