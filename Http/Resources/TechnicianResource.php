<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianResource extends JsonResource
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
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'proficiency' => $this->proficiency,
            'rate' => $this->rate,
            'votes' => $this->votes == null ? 0 : $this->votes,
            'profile_picture' => env('PUBLIC_URL') . ($this->profile_picture == null ? '/profile_pictures/user.png' : $this->profile_picture),
            'created_at' => $this->created_at,
        ];
    }
}
