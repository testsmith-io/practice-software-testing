<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use GraphQL\Error\Error;

class Login
{
    public function __invoke($_, array $args): array
    {
        $token = app('auth')->attempt(['email' => $args['email'], 'password' => $args['password']]);

        if (!$token) {
            throw new Error('Unauthorized');
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => app('auth')->factory()->getTTL() * 60,
        ];
    }
}
