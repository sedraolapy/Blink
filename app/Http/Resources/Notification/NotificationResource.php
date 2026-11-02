<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function __construct($resource,private readonly array $content)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => $data['type'] ?? $this->type,
            'title' => $this->content['title'],
            'message' => $this->content['message'],
            'created_at' => $this->formatDate($this->created_at),
            'read_at' => $this->formatDate($this->read_at),
            'context' => $this->content['context']
        ?? $data['context']
        ?? (object) [],
            ];
    }

    private function formatDate($date): ?string
    {
        return $date?->copy()
            ->timezone('Asia/Damascus')
            ->toIso8601String();
    }
}
