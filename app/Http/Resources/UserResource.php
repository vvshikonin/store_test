<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $roles = RoleResource::collection($this->roles);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $roles,
            'is_active' => $this->is_active,
            'avatar' => $this->avatar,
            'color' => $this->color,
            'permissions' => PermissionResource::collection($this->permissions)
        ];
    }
}
