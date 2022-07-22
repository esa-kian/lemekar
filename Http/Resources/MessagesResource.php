<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessagesResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'content' => $this->content,
            'create_at' => $this->created_at,
            'unread' => $this->unread,
            'receiver_id' => $this->receiver_id,
            'full_name_sender' => ($this->sender_fname ? $this->sender_fname . ' ' . $this->sender_lname : null),
            'sender_picture' => env('PUBLIC_URL') . ($this->sender_pic  == null ? '/profile_pictures/user.png' : $this->sender_pic),
            'sender_id' => $this->sender_id,
            'full_name_receiver' => ($this->receiver_fname ? $this->receiver_fname . ' ' . $this->receiver_lname : null),
            'receiver_picture' => env('PUBLIC_URL') . ($this->receiver_pic  == null ? '/profile_pictures/user.png' : $this->receiver_pic),

        ];
    }
}
