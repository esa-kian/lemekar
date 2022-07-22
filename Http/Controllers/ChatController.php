<?php

namespace App\Http\Controllers;

use App\DB\ChatRepo;
use App\Events\MessageSentEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class ChatController extends Controller
{
    public function index(ChatRepo $chatRepo)
    {
        try {
            if (auth()->guard('api')->user()->client_id) {

                return response(
                    ['messages' => $chatRepo->messagesClient(auth()->guard('api')->id())],
                    200
                );
            } elseif (auth()->guard('api')->user()->technician_id) {

                return response(
                    ['messages' => $chatRepo->messagesTechnician(auth()->guard('api')->id())],
                    200
                );
            }
        } catch (Throwable $e) {

            return response(['error' => $e], 403);
        }
    }

    public function startConversation(Request $request, ChatRepo $chatRepo)
    {
        try {

            return response(
                ['message' => $chatRepo->send(
                    auth()->guard('api')->id(),
                    $request->receiver_id,
                    auth()->guard('api')->id(),
                    null
                )],
                200
            );
        } catch (Throwable $e) {

            return response(['error' => $e], 403);
        }
    }

    public function read(Request $request, ChatRepo $chatRepo)
    {
        try {
            if (auth()->guard('api')->user()->client_id) {

                $messages = $chatRepo->seenClient($request->conversation_id, auth()->guard('api')->id());
            } elseif (auth()->guard('api')->user()->technician_id) {

                $messages = $chatRepo->seenTechnician($request->conversation_id, auth()->guard('api')->id());
            } else {

                $messages = $chatRepo->seenAdmin($request->conversation_id, auth()->guard('api')->id());
            }

            return response(['message' => $messages], 200);
        } catch (Throwable $e) {

            return response(['error' => $e], 404);
        }
    }

    public function fetchConversation(Request $request, ChatRepo $chatRepo)
    {
        try {

            if (auth()->guard('api')->user()->client_id) {

                return response(['messages' => $chatRepo->conversationClient($request->conversation_id)], 200);
            } elseif (auth()->guard('api')->user()->technician_id) {

                return response(['messages' => $chatRepo->conversationTechnician($request->conversation_id)], 200);
            } else {
                return response(['messages' => $chatRepo->conversationAdmin($request->conversation_id)], 200);
            }
        } catch (Throwable $e) {

            return response(['error' => $e], 400);
        }
    }

    public function sendMessage(Request $request, ChatRepo $chatRepo)
    {
        try {

            $message = $chatRepo->send(
                auth()->guard('api')->id(),
                $request->receiver_id,
                $request->sender_id,
                $request->content
            );

            // send event to listener
            if ($request->receiver_id ==  auth()->guard('api')->id()) {

                $this->broadcastMessage($message, User::find($request->receiver_id));
            } elseif ($request->sender_id == auth()->guard('api')->id()) {

                $this->broadcastMessage($message, User::find($request->sender_id));
            }

            return response(['sent' => $message], 200);
        } catch (Throwable $e) {

            return response(['error' => $e], 400);
        }
    }

    public function broadcastMessage($message, $user)
    {
        broadcast(new MessageSentEvent($message, $user));
    }

    public function gotoConversation(Request $request, ChatRepo $chatRepo)
    {
        return response(['conversation' => $chatRepo->findConversation($request->client_id, auth()->guard('api')->id())], 200);
    }

    public function gotoConversationTechnician(Request $request, ChatRepo $chatRepo)
    {
        return response(['conversation' => $chatRepo->findConversationTechnician($request->technician_id, auth()->guard('api')->id())], 200);
    }
}
