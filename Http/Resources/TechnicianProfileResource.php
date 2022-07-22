<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class TechnicianProfileResource extends JsonResource
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
                'national_id' => $this->national_id,
                'telephone' => $this->telephone,
                'address' => $this->address,
                'gender' => $this->gender,
                'iban' => '',
                'city_id' => $this->city_id,
                'proficiency_id' => $this->proficiency_id,
                'national_card_picture' => ($this->national_card_picture == null ? null : env('PUBLIC_URL') . $this->national_card_picture),
                'profile_picture' => env('PUBLIC_URL') . ($this->profile_picture == null ? '/profile_pictures/user.png' : $this->profile_picture),
            ];
        } else {

            return [
                'id' => $this->id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone_number' => $this->phone_number,
                'birthdate' => $this->birthdate,
                'national_id' => $this->national_id,
                'telephone' => $this->telephone,
                'address' => $this->address,
                'gender' => $this->gender,
                'iban' => $this->iban,
                'city_id' => $this->city_id,
                'proficiency_id' => $this->proficiency_id,
                'national_card_picture' => ($this->national_card_picture == null ? null : env('PUBLIC_URL') . $this->national_card_picture),
                'profile_picture' => env('PUBLIC_URL') . ($this->profile_picture == null ? '/profile_pictures/user.png' : $this->profile_picture),
            ];
        }
    }
}
