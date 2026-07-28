<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        // Get all users except the currently logged-in user
        $users = User::where('id', '!=', Auth::id())->get();
        return view('chat', compact('users'));
    }

    public function fetchMessages(User $user)
    {
        $authUserId = Auth::id();

        // Get messages between auth user and selected user
        $messages = Message::where(function($query) use ($authUserId, $user) {
            $query->where('sender_id', $authUserId)->where('receiver_id', $user->id);
        })->orWhere(function($query) use ($authUserId, $user) {
            $query->where('sender_id', $user->id)->where('receiver_id', $authUserId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);

        // Broadcast the event
        broadcast(new MessageSent($message));

        return response()->json($message);
    }
}