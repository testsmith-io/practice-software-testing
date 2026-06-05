<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use App\Models\Invoice;
use App\Services\InvoiceService;
use GraphQL\Error\Error;

class UpdateInvoiceStatus
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function __invoke($_, array $args): Invoice
    {
        $data = ['status' => $args['status']];
        if (isset($args['status_message'])) {
            $data['status_message'] = $args['status_message'];
        }

        $updated = $this->invoiceService->updateInvoiceStatus($args['id'], $data);

        if (!$updated) {
            throw new Error('Invoice not found');
        }

        return Invoice::findOrFail($args['id']);
    }
}
