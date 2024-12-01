<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    public function getCategoriesTree($byCategorySlug = null)
    {
        $cacheKey = $byCategorySlug ? "categories.tree.{$byCategorySlug}" : "categories.tree.all";

        return Cache::remember($cacheKey, 60 * 60, function () use ($byCategorySlug) {
            $query = Category::with('sub_categories')->where("parent_id", "=", null);

            if ($byCategorySlug) {
                $query->where('slug', '=', $byCategorySlug);
            }

            return $query->get();
        });
    }

    public function getAllCategories()
    {
        return Cache::remember('categories.all', 60 * 60, function () {
            return Category::all();
        });
    }

    public function createCategory(array $data)
    {
        $category = Category::create($data);

        Cache::forget('categories.all');
        Cache::forget('categories.tree.all');

        return $category;
    }

    public function getCategoryById($id)
    {
        return Cache::remember("categories.{$id}", 60 * 60, function () use ($id) {
            return Category::with('sub_categories')->findOrFail($id);
        });
    }

    public function searchCategories($query)
    {
        $cacheKey = "categories.search.{$query}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($query) {
            return Category::with('sub_categories')->where('name', 'like', "%$query%")->get();
        });
    }

    public function updateCategory($id, array $data)
    {
        $updated = Category::where('id', $id)->update($data);

        Cache::forget('categories.all');
        Cache::forget("categories.{$id}");
        Cache::forget('categories.tree.all');

        return $updated;
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        Cache::forget('categories.all');
        Cache::forget("categories.{$id}");
        Cache::forget('categories.tree.all');
    }
}
