<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use App\Services\UserService;
use GraphQL\Error\Error;

class Login
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function __invoke($_, array $args): array
    {
        $response = $this->userService->login($args);

        if (isset($response['error'])) {
            throw new Error($response['error']);
        }

        if (isset($response['requires_totp']) && $response['requires_totp']) {
            throw new Error('TOTP verification required');
        }

        $token = $response['token'];

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => app('auth')->factory()->getTTL() * 60,
        ];
    }
}
