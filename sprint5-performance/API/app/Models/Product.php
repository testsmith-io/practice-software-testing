<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mehradsadeghi\FilterQueryString\FilterQueryString;
use Tymon\JWTAuth\Exceptions\JWTException;

/** @OA\Schema(
 *     schema="ProductRequest",
 *     type="object",
 *     title="ProductRequest",
 *     properties={
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="description", type="string"),
 *         @OA\Property(property="price", type="number", example=1.99),
 *         @OA\Property(property="category_id", type="string", example=1),
 *         @OA\Property(property="brand_id", type="string", example=1),
 *         @OA\Property(property="product_image_id", type="string", example=1),
 *         @OA\Property(property="is_location_offer", type="boolean", example=1),
 *         @OA\Property(property="is_rental", type="boolean", example=0),
 *         @OA\Property(property="co2_rating", type="string", enum={"A", "B", "C", "D", "E"}, example="B"),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ProductResponse",
 *     type="object",
 *     title="ProductResponse",
 *     properties={
 *         @OA\Property(property="id", type="string", example=1),
 *         @OA\Property(property="name", type="string", example="new brand"),
 *         @OA\Property(property="description", type="string", example="Lorum ipsum"),
 *         @OA\Property(property="price", type="number", example=9.99),
 *         @OA\Property(property="is_location_offer", type="boolean", example=1),
 *         @OA\Property(property="is_rental", type="boolean", example=0),
 *         @OA\Property(property="in_stock", type="boolean", example=0),
 *         @OA\Property(property="co2_rating", type="string", enum={"A", "B", "C", "D", "E"}, example="B"),
 *         @OA\Property(property="is_eco_friendly", type="boolean", example=true),
 *         @OA\Property(property="brand", ref="#/components/schemas/BrandResponse"),
 *         @OA\Property(property="category", ref="#/components/schemas/CategoryResponse"),
 *         @OA\Property(property="product_image", ref="#/components/schemas/ImageResponse")
 *     }
 * )
 */
class Product extends BaseModel
{
    use HasFactory, FilterQueryString, HasUlids;

    protected $table = 'products';
    protected $fillable = ['name', 'description', 'category_id', 'brand_id', 'price', 'product_image_id', 'is_location_offer', 'is_rental', 'stock', 'co2_rating'];
    protected $hidden = ['brand_id', 'category_id', 'product_image_id', 'stock', 'created_at', 'updated_at'];
    protected $appends = ['in_stock', 'is_eco_friendly'];
    protected $filters = ['between', 'sort'];

    protected $casts = array(
        "price" => "double",
        'is_location_offer' => 'boolean',
        'is_rental' => 'boolean',
    );

    public function product_image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo('App\Models\Brand');
    }

    public function getInStockAttribute()
    {
        try {
            $role = app('auth')->parseToken()->getPayload()->get('role');
            if ($role == "admin") {
                return $this->stock;
            }
        } catch (JWTException $e) {
        }
        return $this->stock > 0;
    }

    public function getIsEcoFriendlyAttribute()
    {
        return in_array(strtoupper($this->co2_rating ?? ''), ['A', 'B']);
    }

    public function scopeEcoFriendly($query)
    {
        return $query->whereIn('co2_rating', ['A', 'B', 'a', 'b']);
    }

    public function scopeWithFilters($query, array $filters)
    {
        return $query->when($filters['eco_friendly'] ?? null, function ($q, $ecoFriendly) {
            if ($ecoFriendly == '1' || $ecoFriendly === true || $ecoFriendly === 'true') {
                return $q->ecoFriendly();
            }
            return $q;
        });
    }

}
