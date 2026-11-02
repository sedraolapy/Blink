<?php

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\DeleteNotificationRequest;
use App\Http\Requests\Notification\IndexNotificationRequest;
use App\Http\Requests\Notification\UpdateReadStatusRequest;
use App\Services\Notification\NotificationService;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {}

    public function index(IndexNotificationRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $notifications = $this->notificationService->index($user,$data);

        return sendResponse(
            __('messages.notifications.retrieved'),
            $notifications
        );
    }


    public function updateReadStatus(UpdateReadStatusRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $result = $this->notificationService->updateReadStatus($user,$data);

        return sendResponse(
            __('messages.notifications.read_status_updated'),
            $result
        );
    }


    public function destroy(DeleteNotificationRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $result = $this->notificationService->delete($user,$data);

        return sendResponse(
            __('messages.notifications.deleted'),
            $result
        );
    }


}
