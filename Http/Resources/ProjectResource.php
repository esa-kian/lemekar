<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->client_status == 'cancel' || $this->client_status == 'current') {
            return [
                'id' => $this->id,
                'details' => $this->details,
                'budget' => $this->budget,
                'start_at' => $this->start_at,
                'vip' => $this->vip,
                'proficiency' => $this->proficiency,
                'address' => $this->address,
                'photos' => PhotoResource::collection($this->photos),
            ];
        } else {

            return [
                'id' => $this->id,
                'details' => $this->details,
                'budget' => $this->budget,
                'start_at' => $this->start_at,
                'vip' => $this->vip,
                'proficiency' => $this->proficiency,
                'address' => $this->address,
                'technician_id' => $this->technician_id,
                'technician_name' => $this->technician->first_name . ' ' . $this->technician->last_name,
                'technician_phone_number' => $this->technician->phone_number,
                'technician_picture' => env('PUBLIC_URL') . ($this->technician->profile_picture == null ? '/profile_pictures/user.png' : $this->technician->profile_picture),
                'photos' => PhotoResource::collection($this->photos),
            ];
        }
    }
}
