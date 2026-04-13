<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/** @OA\Schema(
 *     schema="BrandRequest",
 *     type="object",
 *     title="BrandRequest",
 *     required={"name","slug"},
 *     properties={
 *         @OA\Property(property="name", type="string", maxLength=120, example="new brand", description=""),
 *         @OA\Property(property="slug", type="string", maxLength=120, example="new-brand", description="URL part, words separated by hyphen. Must be unique.")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="BrandResponse",
 *     type="object",
 *     title="BrandResponse",
 *     properties={
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *         @OA\Property(property="name", type="string", example="new brand"),
 *         @OA\Property(property="slug", type="string", example="new-brand")
 *
 *     }
 * )
 */
class Brand extends BaseModel
{
    use HasFactory;

    protected $table = 'brands';
    protected $fillable = ['name', 'slug'];

}
