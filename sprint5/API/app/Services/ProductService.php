<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getAllProducts(array $filters)
    {
        $cacheKey = $this->generateCacheKey($filters);

        return Cache::remember($cacheKey, 60 * 60, function () use ($filters) {
            $query = Product::with('product_image', 'category', 'brand');

            // Filter by category slug
            if (!empty($filters['by_category_slug'])) {
                $categorySlug = $filters['by_category_slug'];
                $categoryIds = DB::table('categories')
                    ->where('slug', $categorySlug)
                    ->orWhereIn('parent_id', function ($query) use ($categorySlug) {
                        $query->select('id')
                            ->from('categories')
                            ->where('slug', $categorySlug);
                    })
                    ->pluck('id');
                $query->whereIn('category_id', $categoryIds);
            }

            // Filter by category
            if (!empty($filters['by_category'])) {
                $query->whereIn('category_id', explode(',', $filters['by_category']));
            }

            // Filter by brand
            if (!empty($filters['by_brand'])) {
                $query->whereIn('brand_id', explode(',', $filters['by_brand']));
            }

            // Search query
            if (!empty($filters['q'])) {
                $query->where('name', 'like', '%' . $filters['q'] . '%');
            }

            // Is rental
            $isRental = $filters['is_rental'] ?? 0;
            $query->where('is_rental', (int)$isRental);

            return $query->filter()->paginate(9);
        });
    }

    protected function generateCacheKey(array $params)
    {
        ksort($params);
        return 'products.index.' . http_build_query($params);
    }

    public function createProduct(array $data)
    {
        $product = Product::create($data);
        $this->clearCache();
        return $product;
    }

    public function getProductById($id)
    {
        $cacheKey = "products.{$id}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($id) {
            return Product::with('product_image', 'category', 'brand')->findOrFail($id);
        });
    }

    public function getRelatedProducts($id)
    {
        $cacheKey = "products.{$id}.related";

        return Cache::remember($cacheKey, 60 * 60, function () use ($id) {
            $categoryId = Product::where('id', $id)->first()->category_id;

            return Product::with('product_image', 'category', 'brand')
                ->where('category_id', $categoryId)
                ->where('id', '!=', $id)
                ->get();
        });
    }

    public function searchProducts($query, $page = 1)
    {
        $cacheKey = "products.search.{$query}.page.{$page}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($query) {
            return Product::with('product_image')
                ->where('name', 'like', "%{$query}%")
                ->paginate(9);
        });
    }

    public function updateProduct($id, array $data)
    {
        $updated = Product::where('id', $id)->update($data);
        $this->clearCache($id);
        return $updated;
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        $this->clearCache($id);
    }

    protected function clearCache($id = null)
    {
        Cache::forget('products.index.*'); // Invalidate index cache
        if ($id) {
            Cache::forget("products.{$id}");
        }
    }

}
