<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\DateHandle;
use App\Models\V1\User;

class InventoryResource extends JsonResource
{
    use DateHandle;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = $this->user;
        $updated_by = User::find($this->updated_by);

        return [
            'id' => $this->id,
            'user_id' => $user->id,
            'username' =>  $user->name,
            'is_completed' => $this->is_completed,
            'updated_by' => $updated_by ? $updated_by->name : null,
            'created_at' => $this->formatIfSet($this->created_at, 'd.m.Y H:i:s'),
            'updated_at' => $this->formatIfSet($this->updated_at, 'd.m.Y H:i:s')
        ];
    }
}
