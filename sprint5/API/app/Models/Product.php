<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 *         @OA\Property(property="category_id", type="string", example="01JFG8Q5XKZJY4BEYQ87PC2Q1Y"),
 *         @OA\Property(property="brand_id", type="string", example="01JFG8Q5XKZJY4BEYQ87PC2Q1Y"),
 *         @OA\Property(property="product_image_id", type="string", example="01JFG8Q5XKZJY4BEYQ87PC2Q1Y"),
 *         @OA\Property(property="is_location_offer", type="boolean", example=1),
 *         @OA\Property(property="is_rental", type="boolean", example=0),
 *         @OA\Property(property="co2_rating", type="string", example="A"),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ProductResponse",
 *     type="object",
 *     title="ProductResponse",
 *     properties={
 *         @OA\Property(property="id", type="string", example="01JFG8Q5XKZJY4BEYQ87PC2Q1Y"),
 *         @OA\Property(property="name", type="string", example="new brand"),
 *         @OA\Property(property="description", type="string", example="Lorum ipsum"),
 *         @OA\Property(property="price", type="number", example=9.99),
 *         @OA\Property(property="is_location_offer", type="boolean", example=1),
 *         @OA\Property(property="is_rental", type="boolean", example=0),
 *         @OA\Property(property="in_stock", type="boolean", example=0),
 *         @OA\Property(property="co2_rating", type="string", example="A"),
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

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class);
    }

    public function getInStockAttribute()
    {
        // Resolve the user once per request and memoize on the request instance.
        // The previous implementation called auth('users')->user() on every
        // serialized product row, which on a paginated overview meant 9+
        // calls to the auth resolver per response.
        static $cachedUser = null;
        static $resolved = false;
        if (!$resolved) {
            $cachedUser = auth('users')->user();
            $resolved = true;
        }

        // If user can view stock details (admin), return actual stock number
        if ($cachedUser && $cachedUser->can('viewStock', $this)) {
            return $this->stock;
        }

        // For regular users and guests, return boolean stock status
        return $this->stock > 0;
    }

    public function getIsEcoFriendlyAttribute()
    {
        return in_array(strtoupper($this->co2_rating ?? ''), ['A', 'B']);
    }

    // Query Scopes for better performance and reusability
    public function scopeWithFilters($query, array $filters)
    {
        return $query->when($filters['by_category'] ?? null, function ($q, $categories) {
            return $q->whereIn('category_id', is_array($categories) ? $categories : explode(',', $categories));
        })->when($filters['by_brand'] ?? null, function ($q, $brands) {
            return $q->whereIn('brand_id', is_array($brands) ? $brands : explode(',', $brands));
        })->when(isset($filters['is_rental']), function ($q) use ($filters) {
            return $q->where('is_rental', $filters['is_rental']);
        })->when($filters['eco_friendly'] ?? null, function ($q, $ecoFriendly) {
            if ($ecoFriendly == '1' || $ecoFriendly === true || $ecoFriendly === 'true') {
                return $q->ecoFriendly();
            }
            return $q;
        })->when($filters['q'] ?? null, function ($q, $search) {
            return $q->where('name', 'like', "%{$search}%");
        })->when($filters['by_spec'] ?? null, function ($q, $specFilter) {
            // Format: "spec_name:value1|value2,spec_name2:value3"
            $specFilters = explode(',', $specFilter);
            foreach ($specFilters as $filter) {
                $parts = explode(':', $filter, 2);
                if (count($parts) === 2) {
                    $specName = trim($parts[0]);
                    $specValues = array_map('trim', explode('|', $parts[1]));
                    $q->whereHas('specs', function ($sq) use ($specName, $specValues) {
                        $sq->where('spec_name', $specName)->whereIn('spec_value', $specValues);
                    });
                }
            }
            return $q;
        });
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByCategory($query, $categoryIds)
    {
        $ids = is_array($categoryIds) ? $categoryIds : explode(',', $categoryIds);
        return $query->whereIn('category_id', $ids);
    }

    public function scopeByBrand($query, $brandIds)
    {
        $ids = is_array($brandIds) ? $brandIds : explode(',', $brandIds);
        return $query->whereIn('brand_id', $ids);
    }

    public function scopeRentals($query, $isRental = true)
    {
        return $query->where('is_rental', $isRental);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', "%{$searchTerm}%");
    }

    public function scopeEcoFriendly($query)
    {
        return $query->whereIn('co2_rating', ['A', 'B', 'a', 'b']);
    }

    public function scopeWithEagerLoading($query)
    {
        return $query->with([
            'product_image:id,by_name,by_url,source_name,source_url,file_name,title',
            'category:id,name,slug',
            'brand:id,name'
        ]);
    }

}
