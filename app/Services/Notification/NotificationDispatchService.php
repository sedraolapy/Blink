<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\DB;

class NotificationDispatchService
{
    public function sendToUser(User $user,string $type,string $eventId,array $context = [])
    {
        $eventKey = $this->eventKey($type, $eventId);

        return DB::transaction(function () use (
            $user,
            $type,
            $eventKey,
            $context
        ) {
            $inserted = DB::table('notification_dispatches')
                ->insertOrIgnore([
                    'user_id' => $user->id,
                    'event_key' => $eventKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($inserted === 0) {
                return false;
            }

            $user->notify(
                new SystemNotification($type, $context)
            );

            return true;
        }, 3);
    }

    public function sendToAllUsers(string $type,string $eventId,array $context = [])
    {
        $sentCount = 0;

        User::query()
            ->select('id')
            ->chunkById(100, function ($users) use (
                $type,
                $eventId,
                $context,
                &$sentCount
            ) {
                foreach ($users as $user) {
                    if ($this->sendToUser(
                        $user,
                        $type,
                        $eventId,
                        $context
                    )) {
                        $sentCount++;
                    }
                }
            });

        return $sentCount;
    }

    private function eventKey(string $type,string $eventId)
    {
        return hash('sha256', "{$type}|{$eventId}");
    }
}
