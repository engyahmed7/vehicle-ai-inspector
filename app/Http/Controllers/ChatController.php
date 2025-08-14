<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function getConversations()
    {
        $user = Auth::user();

        if ($user->role === 'car_owner') {
            $conversations = Conversation::with(['customer', 'car', 'latestMessage'])
                ->where('car_owner_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $conversations = Conversation::with(['carOwner', 'car', 'latestMessage'])
                ->where('customer_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return response()->json($conversations);
    }

    public function getMessages(Request $request, $conversationId)
    {
        $user = Auth::user();

        Conversation::where('id', $conversationId)
            ->where(function($query) use ($user) {
                $query->where('car_owner_id', $user->id)
                      ->orWhere('customer_id', $user->id);
            })
            ->firstOrFail();

        $messagesQuery = Message::with('sender')
            ->where('conversation_id', $conversationId);

        if ($request->has('after')) {
            $messagesQuery->where('id', '>', $request->get('after'));
        }

        $messages = $messagesQuery->orderBy('created_at', 'asc')->get();

        if (!$request->has('after')) {
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'message_type' => 'in:text,image,file'
        ]);

        $user = Auth::user();

        $conversation = Conversation::where('id', $conversationId)
            ->where(function($query) use ($user) {
                $query->where('car_owner_id', $user->id)
                      ->orWhere('customer_id', $user->id);
            })
            ->firstOrFail();

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'content' => $request->content,
            'message_type' => $request->message_type ?? 'text',
        ]);

        $conversation->touch();

        $message->load('sender');

        try {
            broadcast(new MessageSent($message));
            Log::info('Message broadcasted successfully', ['message_id' => $message->id, 'conversation_id' => $conversationId]);
        } catch (\Exception $e) {
            Log::error('Broadcasting failed: ' . $e->getMessage());
        }

        return response()->json($message, 201);
    }

    public function startConversation(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'customer_id' => 'required|exists:users,id',
            'initial_message' => 'required|string|max:1000'
        ]);

        $car = Car::findOrFail($request->car_id);

        $existingConversation = Conversation::where('car_id', $request->car_id)
            ->where('customer_id', $request->customer_id)
            ->where('car_owner_id', $car->user_id)
            ->first();

        if ($existingConversation) {
            return response()->json($existingConversation);
        }

        $conversation = Conversation::create([
            'car_owner_id' => $car->user_id,
            'customer_id' => $request->customer_id,
            'car_id' => $request->car_id,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->customer_id,
            'content' => $request->initial_message,
        ]);

        $conversation->load(['carOwner', 'customer', 'car']);

        return response()->json($conversation, 201);
    }
}
