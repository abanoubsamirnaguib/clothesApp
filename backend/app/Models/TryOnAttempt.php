<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryOnAttempt extends Model
{
    protected $fillable = [
        'product_id',
        'user_key',
        'status',
        'person_image_url',
        'garment_image_url',
        'result_image_url',
        'error',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

