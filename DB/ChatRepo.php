<?php

namespace App\DB;

use App\Http\Resources\MessagesResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatRepo
{
    public function messagesClient($user_id)
    {
        return MessagesResource::collection(DB::table('messages')
            ->select(
                'messages.id',
                'messages.receiver_id',
                'messages.sender_id',
                'messages.conversation_id',
                'messages.content',
                'messages.created_at',
                'messages.unread',
                'tech_sender.first_name as sender_fname',
                'tech_sender.last_name as sender_lname',
                'tech_sender.profile_picture as sender_pic',
                'tech_receiver.first_name as receiver_fname',
                'tech_receiver.last_name as receiver_lname',
                'tech_receiver.profile_picture as receiver_pic'
            )
            ->where('sender_id', $user_id)
            ->orWhere('receiver_id', $user_id)
            ->leftjoin('users as sender', 'sender.id', '=', 'sender_id')
            ->leftjoin('users as receiver', 'receiver.id', '=', 'receiver_id')
            ->leftjoin('technicians as tech_sender', 'tech_sender.id', '=', 'sender.technician_id')
            ->leftjoin('technicians as tech_receiver', 'tech_receiver.id', '=', 'receiver.technician_id')
            ->orderBy('created_at', 'desc')
            ->get()->unique('conversation_id'));
    }

    public function conversationClient($conversation_id)
    {
        return Message::select(
            'id',
            'receiver_id',
            'sender_id',
            'content',
            'created_at'
        )->with('sender:technician_id,id')
            ->where('conversation_id', $conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function seenClient($conversation_id, $receiver_id)
    {
        Message::where('conversation_id', $conversation_id)
            ->where('receiver_id', $receiver_id)
            ->where('unread', 1)
            ->update(['unread' => 0]);

        return $this->conversationClient($conversation_id);
    }

    public function messagesTechnician($user_id)
    {
        return MessagesResource::collection(DB::table('messages')
            ->select(
                'messages.id',
                'messages.receiver_id',
                'messages.sender_id',
                'messages.conversation_id',
                'messages.content',
                'messages.created_at',
                'messages.unread',
                'client_sender.first_name as sender_fname',
                'client_sender.last_name as sender_lname',
                'client_sender.profile_picture as sender_pic',
                'client_receiver.first_name as receiver_fname',
                'client_receiver.last_name as receiver_lname',
                'client_receiver.profile_picture as receiver_pic',

            )
            ->where('sender_id', $user_id)
            ->orWhere('receiver_id', $user_id)
            ->leftjoin('users as sender', 'sender.id', '=', 'sender_id')
            ->leftjoin('users as receiver', 'receiver.id', '=', 'receiver_id')
            ->leftjoin('clients as client_sender', 'client_sender.id', '=', 'sender.client_id')
            ->leftjoin('clients as client_receiver', 'client_receiver.id', '=', 'receiver.client_id')
            ->orderBy('created_at', 'desc')
            ->get()->unique('conversation_id'));
    }

    public function conversationTechnician($conversation_id)
    {
        return Message::select(
            'id',
            'receiver_id',
            'sender_id',
            'content',
            'created_at'
        )->with('sender:client_id,id')
            ->where('conversation_id', $conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function seenTechnician($conversation_id, $receiver_id)
    {
        Message::where('conversation_id', $conversation_id)
            ->where('receiver_id', $receiver_id)
            ->where('unread', 1)
            ->update(['unread' => 0]);

        return $this->conversationTechnician($conversation_id);
    }

    public function conversationAdmin($conversation_id)
    {
        return Message::select(
            'id',
            'receiver_id',
            'sender_id',
            'content',
            'created_at'
        )->with('sender')
            ->where('conversation_id', $conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function seenAdmin($conversation_id, $receiver_id)
    {
        Message::where('conversation_id', $conversation_id)
            ->where('receiver_id', $receiver_id)
            ->where('unread', 1)
            ->update(['unread' => 0]);

        return $this->conversationAdmin($conversation_id);
    }

    public function send($user_id, $receiver_id, $sender_id, $content)
    {
        $message = resolve(Message::class);

        $message->sender_id = $user_id;
        if ($sender_id == $user_id) {

            $message->receiver_id = $receiver_id;
        } else {

            $message->receiver_id = $sender_id;
        }

        $message->conversation_id = $this->setConverastionId($user_id, $message->receiver_id);

        $message->content = $content;

        $message->unread = 1;

        $message->save();

        return $message;
    }

    public function setConverastionId($user_id, $receiver_id)
    {
        $conversation_id = Message::where('conversation_id', $receiver_id . '_' . $user_id)->get('conversation_id');

        if (count($conversation_id) > 0) {

            return $receiver_id . '_' . $user_id;
        } else {

            return $user_id . '_' . $receiver_id;
        }
    }

    public function findConversation($client_id, $user_id)
    {
        $user = User::where('client_id', $client_id)->get(['id']);

        foreach ($user as $u) {

            return Message::where('sender_id', $u->id)
                ->where('receiver_id', $user_id)->first();
        }
    }

    public function findConversationTechnician($technician_id, $user_id)
    {
        $user = User::where('technician_id', $technician_id)->get(['id']);

        foreach ($user as $u) {

            return Message::where('sender_id', $u->id)
                ->where('receiver_id', $user_id)->first();
        }
    }
}
