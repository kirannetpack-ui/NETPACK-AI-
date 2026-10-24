<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Display the chat index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $contacts = $this->getContacts($user);
        
        return view('chat.index', compact('contacts'));
    }

    /**
     * Get conversations for the user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function conversations()
    {
        $user = Auth::user();
        $contacts = $this->getContacts($user);
        
        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Send a chat message.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        // Broadcast the message (if using Laravel Echo)
        // event(new NewChatMessage($message));

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
    }

    /**
     * Get messages for a conversation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function messages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user_id;
        $authId = Auth::id();

        $messages = ChatMessage::where(function($query) use ($authId, $userId) {
            $query->where('sender_id', $authId)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($authId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')
          ->get();

        // Mark messages as read
        ChatMessage::where('sender_id', $userId)
                   ->where('receiver_id', $authId)
                   ->where('is_read', false)
                   ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Get contacts for the user.
     *
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    private function getContacts($user)
    {
        // Get all users who have had conversations with the current user
        $contactIds = ChatMessage::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->pluck('sender_id')
            ->merge(ChatMessage::where('receiver_id', $user->id)->pluck('receiver_id'))
            ->unique()
            ->filter(function($id) use ($user) {
                return $id != $user->id;
            });

        $contacts = User::whereIn('id', $contactIds)
            ->get()
            ->map(function($contact) use ($user) {
                // Get the last message
                $lastMessage = ChatMessage::where(function($query) use ($user, $contact) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $contact->id);
                })->orWhere(function($query) use ($user, $contact) {
                    $query->where('sender_id', $contact->id)
                          ->where('receiver_id', $user->id);
                })->latest()->first();

                // Count unread messages
                $unreadCount = ChatMessage::where('sender_id', $contact->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                return (object) [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'profile_photo' => $contact->profile_photo,
                    'last_message' => $lastMessage ? $lastMessage->message : null,
                    'last_message_time' => $lastMessage ? $lastMessage->created_at->diffForHumans() : null,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc('last_message_time')
            ->values();

        return $contacts;
    }
}