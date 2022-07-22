<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => ($this->client_fname ? $this->client_fname . ' ' . $this->client_lname : $this->technician_fname . ' ' . $this->technician_lname),
        ];
    }
}
