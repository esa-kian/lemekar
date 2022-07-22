<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ClientDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if ($this->bank_information_id == null) {

            return [
                'id' => $this->id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone_number' => $this->phone_number,
                'birthdate' => $this->birthdate,
                'gender' => $this->gender,
                'profile_picture' => env('PUBLIC_URL') . $this->profile_picture,
            ];
        } else {

            return [
                'id' => $this->id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone_number' => $this->phone_number,
                'birthdate' => $this->birthdate,
                'gender' => $this->gender,
                'bank_information' => $this->bankInformation,
                'profile_picture' => env('PUBLIC_URL') . $this->profile_picture,
            ];
        }
    }
}
