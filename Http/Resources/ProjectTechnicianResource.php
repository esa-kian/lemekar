<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ProjectTechnicianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->technician_status == null) {

            return [
                'id' => $this->id,
                'details' => $this->details,
                'budget' => $this->budget,
                'start_at' => $this->start_at,
                'vip' => $this->vip,
                'proficiency' => $this->proficiency->title,
                'skills' => $this->skills,
                'address' => $this->address->city->title . ', ' . $this->address->town->title,
                'client_id' => $this->client_id,
                'client_name' => $this->client->first_name . ' ' . $this->client->last_name,
                'client_picture' => env('PUBLIC_URL') . ($this->client->profile_picture  == null ? '/profile_pictures/user.png' : $this->client->profile_picture ),
                'client_date' => $this->client->created_at,
                'photos' => PhotoResource::collection($this->photos),
            ];
        } else {

            return [
                'id' => $this->id,
                'details' => $this->details,
                'budget' => $this->budget,
                'start_at' => $this->start_at,
                'vip' => $this->vip,
                'proficiency' => $this->proficiency->title,
                // 'skills' => $this->skills,
                'address' => $this->address->city->title . ', ' . $this->address->town->title . ', ' . $this->address->description,
                'lat' => $this->address->lat,
                'long' => $this->address->long,
                'client_id' => $this->client_id,
                'client_name' => $this->client->first_name . ' ' . $this->client->last_name,
                'client_picture' => env('PUBLIC_URL') . ($this->client->profile_picture  == null ? '/profile_pictures/user.png' : $this->client->profile_picture ),
                'client_date' => $this->client->created_at,
                'photos' => PhotoResource::collection($this->photos),
            ];
        }
    }
}
