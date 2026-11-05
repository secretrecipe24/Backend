<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Message;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function clientConversation(Request $request)
    {
        $match = UserMatch::query()
            ->with('coach:id,name,email')
            ->where('client_id', $request->user()->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (! $match) {
            return response()->json(['coach' => null, 'messages' => []]);
        }

        $messages = $this->messagesBetween(
            User::class,
            $request->user()->id,
            Coach::class,
            $match->coach_id
        );

        Message::query()
            ->where('sender_id', $match->coach_id)
            ->where('sender_type', Coach::class)
            ->where('receiver_id', $request->user()->id)
            ->where('receiver_type', User::class)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'coach' => $match->coach,
            'messages' => $messages,
        ]);
    }

    public function sendToCoach(Request $request)
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        if (! $request->filled('content') && ! $request->hasFile('image')) {
            return response()->json(['message' => 'Write a message or attach an image.'], 422);
        }

        $match = UserMatch::query()
            ->where('client_id', $request->user()->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (! $match) {
            return response()->json(['message' => 'You do not have an active coach.'], 409);
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'sender_type' => User::class,
            'receiver_id' => $match->coach_id,
            'receiver_type' => Coach::class,
            'content' => trim((string) ($validated['content'] ?? '')),
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('messages', 'public') : null,
        ]);

        return response()->json(['message' => $message], 201);
    }

    public function coachConversations(Request $request)
    {
        $coach = $request->user();
        $matches = UserMatch::query()
            ->with('client:id,name,email')
            ->where('coach_id', $coach->id)
            ->where('status', 'active')
            ->latest('id')
            ->get();

        $clients = $matches->map(function (UserMatch $match) use ($coach) {
            $lastMessage = Message::query()
                ->where(function ($query) use ($coach, $match) {
                    $query->where(function ($inner) use ($coach, $match) {
                        $inner->where('sender_id', $coach->id)
                            ->where('sender_type', Coach::class)
                            ->where('receiver_id', $match->client_id)
                            ->where('receiver_type', User::class);
                    })->orWhere(function ($inner) use ($coach, $match) {
                        $inner->where('sender_id', $match->client_id)
                            ->where('sender_type', User::class)
                            ->where('receiver_id', $coach->id)
                            ->where('receiver_type', Coach::class);
                    });
                })
                ->latest('id')
                ->first();

            return [
                'id' => $match->client_id,
                'name' => $match->client?->name,
                'email' => $match->client?->email,
                'last_message' => $lastMessage,
            ];
        })->values();

        return response()->json(['clients' => $clients]);
    }

    public function coachConversation(Request $request, int $clientId)
    {
        $coach = $request->user();
        $this->ensureActiveMatch($coach->id, $clientId);

        Message::query()
            ->where('sender_id', $clientId)
            ->where('sender_type', User::class)
            ->where('receiver_id', $coach->id)
            ->where('receiver_type', Coach::class)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'client' => User::query()->select('id', 'name', 'email')->findOrFail($clientId),
            'messages' => $this->messagesBetween(Coach::class, $coach->id, User::class, $clientId),
        ]);
    }

    public function sendToClient(Request $request, int $clientId)
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        if (! $request->filled('content') && ! $request->hasFile('image')) {
            return response()->json(['message' => 'Write a message or attach an image.'], 422);
        }

        $coach = $request->user();
        $this->ensureActiveMatch($coach->id, $clientId);

        $message = Message::create([
            'sender_id' => $coach->id,
            'sender_type' => Coach::class,
            'receiver_id' => $clientId,
            'receiver_type' => User::class,
            'content' => trim((string) ($validated['content'] ?? '')),
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('messages', 'public') : null,
        ]);

        return response()->json(['message' => $message], 201);
    }

    private function messagesBetween(string $senderType, int $senderId, string $receiverType, int $receiverId)
    {
        return Message::query()
            ->where(function ($query) use ($senderType, $senderId, $receiverType, $receiverId) {
                $query->where(function ($inner) use ($senderType, $senderId, $receiverType, $receiverId) {
                    $inner->where('sender_id', $senderId)
                        ->where('sender_type', $senderType)
                        ->where('receiver_id', $receiverId)
                        ->where('receiver_type', $receiverType);
                })->orWhere(function ($inner) use ($senderType, $senderId, $receiverType, $receiverId) {
                    $inner->where('sender_id', $receiverId)
                        ->where('sender_type', $receiverType)
                        ->where('receiver_id', $senderId)
                        ->where('receiver_type', $senderType);
                });
            })
            ->orderBy('id')
            ->get();
    }

    private function ensureActiveMatch(int $coachId, int $clientId): void
    {
        abort_unless(
            UserMatch::query()
                ->where('coach_id', $coachId)
                ->where('client_id', $clientId)
                ->where('status', 'active')
                ->exists(),
            403,
            'This client is not an active client of this coach.'
        );
    }
}
