<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function getCategoriesTree($byCategorySlug = null)
    {
        $cacheKey = $byCategorySlug ? "categories.tree.{$byCategorySlug}" : "categories.tree.all";
        Log::debug("Fetching category tree", ['slug' => $byCategorySlug, 'cache_key' => $cacheKey]);

        return Cache::remember($cacheKey, 60 * 60, function () use ($byCategorySlug) {
            Log::debug("Cache miss for category tree", ['slug' => $byCategorySlug]);

            $query = Category::with('sub_categories')->where("parent_id", "=", null);

            if ($byCategorySlug) {
                $query->where('slug', '=', $byCategorySlug);
            }

            $results = $query->get();

            Log::debug("Category tree fetched from DB", [
                'slug' => $byCategorySlug,
                'count' => $results->count(),
            ]);

            return $results;
        });
    }

    public function getAllCategories()
    {
        Log::debug("Fetching all categories from cache");

        return Cache::remember('categories.all', 60 * 60, function () {
            Log::debug("Cache miss: fetching all categories from DB");

            $categories = Category::all();

            Log::debug("Fetched all categories", ['count' => $categories->count()]);

            return $categories;
        });
    }

    public function createCategory(array $data)
    {
        Log::info("Creating category", ['data' => $data]);

        $category = Category::create($data);

        Log::debug("Category created", ['id' => $category->id]);

        Cache::forget('categories.all');
        Cache::forget('categories.tree.all');

        Log::debug("Cache invalidated after category creation");

        return $category;
    }

    public function getCategoryById($id)
    {
        Log::debug("Fetching category by ID", ['id' => $id]);

        return Cache::remember("categories.{$id}", 60 * 60, function () use ($id) {
            Log::debug("Cache miss: fetching category from DB", ['id' => $id]);

            $category = Category::with('sub_categories')->findOrFail($id);

            Log::debug("Fetched category", ['id' => $category->id]);

            return $category;
        });
    }

    public function searchCategories($query)
    {
        $cacheKey = "categories.search.{$query}";
        Log::debug("Searching categories", ['query' => $query, 'cache_key' => $cacheKey]);

        return Cache::remember($cacheKey, 60 * 60, function () use ($query) {
            Log::debug("Cache miss: searching categories in DB", ['query' => $query]);

            $results = Category::with('sub_categories')
                ->where('name', 'like', "%$query%")
                ->get();

            Log::debug("Search results fetched", ['query' => $query, 'count' => $results->count()]);

            return $results;
        });
    }

    public function updateCategory($id, array $data)
    {
        Log::info("Updating category", ['id' => $id, 'data' => $data]);

        $updated = Category::where('id', $id)->update($data);

        Cache::forget('categories.all');
        Cache::forget("categories.{$id}");
        Cache::forget('categories.tree.all');

        Log::debug("Cache invalidated after update", ['id' => $id]);

        return $updated;
    }

    public function deleteCategory($id)
    {
        Log::info("Deleting category", ['id' => $id]);

        $category = Category::findOrFail($id);
        $category->delete();

        Cache::forget('categories.all');
        Cache::forget("categories.{$id}");
        Cache::forget('categories.tree.all');

        Log::debug("Cache invalidated after deletion", ['id' => $id]);
    }
}
