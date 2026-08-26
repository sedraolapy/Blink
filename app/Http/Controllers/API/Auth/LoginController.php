<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $result = $this->authService->login($data);

        if (! $result) {
            return sendError(
                __('messages.invalid_credentials'),
                401
            );
        }

        return sendResponse(
            __('messages.login_success'),
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return sendResponse(
            __('messages.logout_success')
        );
    }
}