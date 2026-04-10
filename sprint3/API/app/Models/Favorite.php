<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends BaseModel
{
    protected $table = 'favorites';
    protected $fillable = ['user_id', 'product_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
