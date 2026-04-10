<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use App\Models\User;

class Register
{
    public function __invoke($_, array $args)
    {
        $args['role'] = 'user';
        $args['password'] = app('hash')->make($args['password']);

        return User::create($args);
    }
}
