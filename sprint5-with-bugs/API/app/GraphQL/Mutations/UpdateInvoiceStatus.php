<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use App\Models\Invoice;
use GraphQL\Error\Error;

class UpdateInvoiceStatus
{
    public function __invoke($_, array $args): Invoice
    {
        $invoice = Invoice::find($args['id']);

        if (!$invoice) {
            throw new Error('Invoice not found');
        }

        $data = ['status' => $args['status']];

        if (isset($args['status_message'])) {
            $data['status_message'] = $args['status_message'];
        }

        $invoice->update($data);

        return $invoice->fresh();
    }
}
