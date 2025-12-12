<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'created_by' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'participants' => $this->participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->name,
                ];
            }),
            'latest_message' => $this->when($this->relationLoaded('latestMessage') && $this->latestMessage, function () {
                return [
                    'id' => $this->latestMessage->id,
                    'body' => $this->latestMessage->body,
                    'created_at' => $this->latestMessage->created_at?->toIso8601String(),
                    'user' => [
                        'id' => $this->latestMessage->user->id,
                        'name' => $this->latestMessage->user->name,
                    ],
                ];
            }),
            'unread_count' => $this->when(isset($this->unread_count), $this->unread_count ?? 0),
            'messages' => $this->when($this->relationLoaded('messages') && is_object($this->messages), function () {
                return [
                    'data' => MessageResource::collection($this->messages),
                    'current_page' => $this->messages->currentPage(),
                    'last_page' => $this->messages->lastPage(),
                    'per_page' => $this->messages->perPage(),
                    'total' => $this->messages->total(),
                ];
            }),
        ];
    }
}
