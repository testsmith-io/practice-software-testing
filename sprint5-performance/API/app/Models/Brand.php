<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mehradsadeghi\FilterQueryString\FilterQueryString;

/** @OA\Schema(
 *     schema="BrandRequest",
 *     type="object",
 *     title="BrandRequest",
 *     properties={
 *         @OA\Property(property="name", type="string", example="new brand", description=""),
 *         @OA\Property(property="slug", type="string", example="new-brand", description="URL part, words separated by hyphen")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="BrandResponse",
 *     type="object",
 *     title="BrandResponse",
 *     properties={
 *     @OA\Property(property="id", type="string"),
 *         @OA\Property(property="name", type="string", example="new brand"),
 *         @OA\Property(property="slug", type="string", example="new-brand")
 *
 *     }
 * )
 */
class Brand extends BaseModel
{
    use HasFactory, FilterQueryString, HasUlids;

    protected $table = 'brands';
    protected $fillable = ['name', 'slug'];
    protected $filters = ['sort'];

}
