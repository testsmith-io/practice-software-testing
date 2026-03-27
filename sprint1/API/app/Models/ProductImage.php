<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="ImageResponse",
 *     type="object",
 *     title="ImageResponse",
 *     properties={
 *         @OA\Property(property="by_name", type="string"),
 *         @OA\Property(property="by_url", type="string"),
 *         @OA\Property(property="source_name", type="string"),
 *         @OA\Property(property="source_url", type="string"),
 *         @OA\Property(property="file_name", type="string"),
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="id", type="integer")
 *     }
 * )
 */
class ProductImage extends BaseModel
{
    use HasFactory;

    protected $table = 'product_images';

}
