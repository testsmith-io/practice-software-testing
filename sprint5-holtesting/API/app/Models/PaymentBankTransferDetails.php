<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentBankTransferDetails extends BaseModel
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected $hidden = ['id', 'created_at', 'updated_at'];


    public function payment()
    {
        return $this->morphOne(Payment::class, 'payment_details');
    }

}
