<?php

namespace App\Services;

use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function getAllProducts(array $filters)
    {
        if (isset($filters['is_rental'])) {
            $value = strtolower((string) $filters['is_rental']);
            $filters['is_rental'] = in_array($value, ['1', 'true'], true) ? 1 : 0;
        }
        // Don't default is_rental - allow fetching all products when not specified

        $cacheKey = $this->generateCacheKey($filters);

        Log::debug("Fetching all products with filters", ['filters' => $filters, 'cacheKey' => $cacheKey]);

        return Cache::remember($cacheKey, 60 * 60, function () use ($filters) {
            $query = Product::withEagerLoading()
                ->select('id', 'name', 'description', 'price', 'product_image_id', 'category_id', 'brand_id', 'is_location_offer', 'is_rental', 'stock', 'co2_rating');

            if (!empty($filters['by_category_slug'])) {
                $categorySlug = $filters['by_category_slug'];
                Log::debug("Filtering by category slug", ['slug' => $categorySlug]);

                $categoryIds = DB::table('categories')
                    ->where('slug', $categorySlug)
                    ->orWhereIn('parent_id', function ($query) use ($categorySlug) {
                        $query->select('id')
                            ->from('categories')
                            ->where('slug', $categorySlug);
                    })
                    ->pluck('id');

                $query->byCategory($categoryIds->toArray());
            }

            // Apply filters using scopes
            $query->withFilters($filters);

            $results = $query->filter()->paginate(9);
            Log::debug("Product query executed", ['result_count' => $results->total()]);

            return $results;
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

        Log::info("Product created", ['id' => $product->id]);

        $product->load('product_image', 'category', 'brand');
        return $product;
    }

    public function getProductById($id)
    {
        $cacheKey = "products.{$id}";

        Log::debug("Fetching product by ID", ['id' => $id]);

        return Cache::remember($cacheKey, 60 * 60, function () use ($id) {
            $product = Product::with([
                'product_image:id,by_name,by_url,source_name,source_url,file_name,title',
                'category:id,name,slug,parent_id',
                'brand:id,name'
            ])->findOrFail($id);

            // Make hidden fields visible for admin editing
            $product->makeVisible(['stock', 'brand_id', 'category_id', 'product_image_id']);

            return $product;
        });
    }

    public function getRelatedProducts($id)
    {
        $cacheKey = "products.{$id}.related";

        Log::debug("Fetching related products", ['id' => $id]);

        return Cache::remember($cacheKey, 60 * 60, function () use ($id) {
            $product = Product::select('id', 'category_id')->find($id);

            if (!$product) {
                Log::error("Related products fetch failed — product not found", ['id' => $id]);
                throw new Exception("Product with ID {$id} not found");
            }

            $categoryId = $product->category_id;

            $related = Product::with([
                'product_image:id,by_name,by_url,source_name,source_url,file_name,title',
                'category:id,name',
                'brand:id,name'
            ])
            ->select('id', 'name', 'description', 'price', 'category_id', 'brand_id', 'product_image_id', 'is_location_offer', 'is_rental', 'stock', 'co2_rating')
            ->where('category_id', $categoryId)
            ->where('id', '!=', $id)
            ->limit(10)
            ->get();

            Log::debug("Related products fetched", ['count' => $related->count()]);

            return $related;
        });
    }

    public function searchProducts($query, $page = 1)
    {
        $cacheKey = "products.search.{$query}.page.{$page}";

        Log::debug("Searching products", ['query' => $query, 'page' => $page]);

        return Cache::remember($cacheKey, 60 * 60, function () use ($query) {
            $results = Product::with([
                'product_image:id,by_name,by_url,source_name,source_url,file_name,title',
                'category:id,name',
                'brand:id,name'
            ])
            ->select('id', 'name', 'description', 'price', 'product_image_id', 'category_id', 'brand_id', 'is_location_offer', 'is_rental', 'stock', 'co2_rating')
            ->where('name', 'like', "%{$query}%")
            ->paginate(9);

            Log::debug("Search results found", ['total' => $results->total()]);
            return $results;
        });
    }

    public function updateProduct($id, array $data)
    {
        Log::info("Updating product", ['id' => $id]);

        $updated = Product::where('id', $id)->update($data);
        $this->clearCache($id);

        Log::debug("Product updated", ['id' => $id, 'status' => $updated ? 'success' : 'failed']);

        return $updated;
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        $this->clearCache($id);

        Log::warning("Product deleted", ['id' => $id]);
    }

    protected function clearCache($id = null)
    {
        Log::debug("Clearing cache", ['id' => $id]);

        // Clear all product list caches by using cache tags
        Cache::flush();

        if ($id) {
            Cache::forget("products.{$id}");
            Cache::forget("products.{$id}.related");
            // Clear any search caches that might include this product
            Cache::flush();
        }
    }
}
