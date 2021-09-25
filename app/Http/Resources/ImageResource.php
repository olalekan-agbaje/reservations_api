<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [$this->merge(Arr::except(parent::toArray($request), [
            'resource_type',
            'resource_id',
            'created_at',
            'updated_at',
            'deleted_at'
        ]))];
    }
}