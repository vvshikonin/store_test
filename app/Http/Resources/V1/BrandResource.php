<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $updated_at = $this->updated_at;

        $updated_at = $updated_at ? $updated_at->format('d.m.Y H:i:s') : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'marginality' => $this->marginality,
            'maintained_marginality' => $this->maintained_marginality,
            'xml_link' => $this->xml_link,
            'updated_at' => $updated_at
        ];
    }
}
