<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use App\Services\UserService;

class Register
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function __invoke($_, array $args)
    {
        return $this->userService->registerUser($args);
    }
}
