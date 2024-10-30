<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\V1\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Models\V1\RoleUser;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!$this->check_permission()){
            return response('Недостаточно прав для совершения этого действия этого действия!',403);
        }

        $users = User::where('name', 'LIKE', "%$request->name_filter%")
            ->orderBy($request->sort_field, $request->sort_type)
            ->paginate($request->per_page);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!$this->check_permission() && auth()->user()->id != $id){
            return response('Недостаточно прав для совершения этого действия этого действия!',403);
        }

        $user = User::find($id);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $user->update($request->all());

        if($request->has('new_avatar')){
            $file = $request->file('new_avatar');
            $user->avatar = $this->storeUserAvatar($file);

            $user->save();
        }
        if($request->has('new_password')){
            $new_password = Hash::make($request->new_password);
            $user->password = $new_password;
            $user->save();
        }

        if($this->check_permission()){
            if ($request->has('new_roles_ids')){
                foreach($request->new_roles_ids as $new_role_id){
                    RoleUser::create([
                        'user_id' => $user->id,
                        'role_id' => $new_role_id,
                    ]);
                }
            }
            if ($request->has('deleted_roles_ids')){
                foreach($request->deleted_roles_ids as $deleted_role_id){
                    RoleUser::where('user_id', $user->id)->where('role_id', $deleted_role_id)->delete();
                }
            }
        }

        return new UserResource($user->refresh());
    }
    public function delete_avatar($id)
    {
        $user = User::find($id);

        if ($user->avatar) {
            $user->avatar = null;
            $user->save();
        }

        return new UserResource($user->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        //
    }

    private function check_permission(){
        $user = auth()->user();
        $user = $user->refresh();
        $user_perrmissions = $user->permissions;
        if(!$user_perrmissions->where('name', 'users_managment')->count()){
            return false;
        }
        return true;
    }

    private function storeUserAvatar($file){
        $extension = $file->getClientOriginalExtension();
        $fileName = hash('sha256', $file->get());
        return $file->storeAs('user_avatar', $fileName.'.'.$extension, 'public');
    }
}
