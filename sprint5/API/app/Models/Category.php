<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @OA\Schema(
 *     schema="CategoryRequest",
 *     type="object",
 *     title="CategoryRequest",
 *     properties={
 *         @OA\Property(property="name", type="string", example="new category", description=""),
 *         @OA\Property(property="slug", type="string", example="new-category", description="URL part, words separated by hyphen")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CategoryResponse",
 *     type="object",
 *     title="CategoryResponse",
 *     properties={
 *         @OA\Property(property="id", type="string"),
 *         @OA\Property(property="parent_id", type="string"),
 *         @OA\Property(property="name", type="string", example="new category"),
 *         @OA\Property(property="slug", type="string", example="new-category")
 *     }
 * )
 */
class Category extends BaseModel
{

    use HasFactory, HasUlids;

    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'parent_id'];

    public function parent_category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function sub_categories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->with('sub_categories');
    }

}
