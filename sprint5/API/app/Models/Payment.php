<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends BaseModel
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected $hidden = ['id', 'invoice_id', 'payment_details_id', 'payment_details_type', 'created_at', 'updated_at'];

    protected $casts = [
        'payment_details' => 'array',  // Cast payment_details to an array
    ];
    public function payment_details()
    {
        return $this->morphTo();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}
