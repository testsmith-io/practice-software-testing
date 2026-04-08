<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use App\Models\ContactRequests;
use GraphQL\Error\Error;

class UpdateContactMessageStatus
{
    public function __invoke($_, array $args): ContactRequests
    {
        $message = ContactRequests::find($args['id']);

        if (!$message) {
            throw new Error('Contact message not found');
        }

        $message->update(['status' => $args['status']]);

        return $message->fresh();
    }
}
