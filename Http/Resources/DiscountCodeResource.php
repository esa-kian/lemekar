<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
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
            'proficiency' => $this->proficiency,
            'code' => $this->code,
            'picture' => env('PUBLIC_URL') . $this->picture,
        ];
    }
}
