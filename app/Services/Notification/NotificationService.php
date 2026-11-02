<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Http\Resources\Notification\NotificationResource;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationService
{
    public function index(User $user, array $filters)
    {
        $filter = $filters['filter'] ?? 'all';
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);


        $customerIds = $notifications
        ->getCollection()
        ->map(
            fn ($notification) => data_get($notification->data, 'context.customer_id')
        )
        ->filter()
        ->unique()
        ->values();

        $customers = $customerIds->isEmpty()
        ? collect()
        : Customer::query()
            ->whereIn('id', $customerIds)
            ->get()
            ->keyBy('id');

        $resolver = new NotificationContentResolver();


        return [
            'unread_count' => $user->unreadNotifications()->count(),
            
            'items' => $notifications
            ->getCollection()
            ->map(
                fn ($notification) => (new NotificationResource(
                    $notification,
                    $resolver->resolve($notification, $customers)
                ))->resolve()
            )
            ->values()
            ->all(),

            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ];
    }

    public function updateReadStatus(User $user, array $data): array
    {
        $isRead = (bool) $data['is_read'];
        $query = $user->notifications();

        if (! ($data['all'] ?? false)) {
            $query->whereIn(
                'id',
                $data['notification_ids']
            );
        }

        if ($isRead) {
            $query->whereNull('read_at');
        } else {
            $query->whereNotNull('read_at');
        }

        $updatedCount = $query->update([
            'read_at' => $isRead ? now() : null,
        ]);

        return [
            'updated_count' => $updatedCount,
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }


    public function delete(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {

            $ids = $data['notification_ids'];

            $notifications = $user->notifications()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get(['id']);

            if ($notifications->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'notification_ids' => [
                        __('validation.notifications.delete.not_found'),
                    ],
                ]);
            }

            $deletedCount = $user->notifications()
                ->whereIn('id', $ids)
                ->delete();

            return [
                'deleted_ids' => $ids,
                'deleted_count' => $deletedCount,
                'unread_count' => $user->unreadNotifications()->count(),
            ];
        });
    }


}
