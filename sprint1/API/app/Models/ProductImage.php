<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="ImageResponse",
 *     type="object",
 *     title="ImageResponse",
 *     properties={
 *         @OA\Property(property="by_name", type="string", nullable=true),
 *         @OA\Property(property="by_url", type="string", nullable=true),
 *         @OA\Property(property="source_name", type="string", nullable=true),
 *         @OA\Property(property="source_url", type="string", nullable=true),
 *         @OA\Property(property="file_name", type="string", nullable=true),
 *         @OA\Property(property="title", type="string", nullable=true),
 *         @OA\Property(property="id", type="integer", readOnly=true)
 *     }
 * )
 */
class ProductImage extends BaseModel
{
    use HasFactory;

    protected $table = 'product_images';

}
