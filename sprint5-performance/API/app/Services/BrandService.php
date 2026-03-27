<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BrandService
{
    public function getAllBrands()
    {
        Log::debug('Fetching all brands from cache or DB');

        return Cache::remember('brands.all', 60 * 60, function () {
            Log::info('Cache miss for all brands. Querying DB.');
            return Brand::all();
        });
    }

    public function createBrand(array $data)
    {
        Log::info('Creating new brand', $data);

        $brand = Brand::create($data);

        Cache::forget('brands.all');

        Log::debug('Cache cleared for brands.all after creating brand ID: ' . $brand->id);

        return $brand;
    }

    public function getBrandById($id)
    {
        Log::debug("Fetching brand ID: {$id} from cache or DB");

        return Cache::remember("brands.{$id}", 60 * 60, function () use ($id) {
            Log::info("Cache miss for brand ID: {$id}. Querying DB.");
            return Brand::findOrFail($id);
        });
    }

    public function searchBrands($query)
    {
        $cacheKey = "brands.search.{$query}";

        Log::debug("Searching brands with query: '{$query}'");

        return Cache::remember($cacheKey, 60 * 60, function () use ($query) {
            Log::info("Cache miss for brand search: '{$query}'. Querying DB.");
            return Brand::where('name', 'like', "%$query%")->get();
        });
    }

    public function updateBrand($id, array $data)
    {
        Log::info("Updating brand ID: {$id}", $data);

        $updated = Brand::where('id', $id)->update($data);

        Cache::forget('brands.all');
        Cache::forget("brands.{$id}");

        Log::debug("Cache cleared for brands.all and brands.{$id} after update");

        return $updated;
    }

    public function deleteBrand($id)
    {
        Log::info("Deleting brand ID: {$id}");

        $brand = Brand::findOrFail($id);
        $brand->delete();

        Cache::forget('brands.all');
        Cache::forget("brands.{$id}");

        Log::debug("Brand ID: {$id} deleted and related cache cleared");
    }
}
