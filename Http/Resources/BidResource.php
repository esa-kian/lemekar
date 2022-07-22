<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
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
            'user_id' => $this->user_id,
            'technician_id' => $this->technician_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'rate' => $this->rate,
            'votes' => $this->votes,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'profile_picture' => env('PUBLIC_URL') . $this->profile_picture,
        ];
    }
}
