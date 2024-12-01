<?php


namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;

class BrandService
{
    public function getAllBrands()
    {
        return Cache::remember('brands.all', 60 * 60, function () {
            return Brand::all();
        });
    }

    public function createBrand(array $data)
    {
        $brand = Brand::create($data);
        Cache::forget('brands.all');
        return $brand;
    }

    public function getBrandById($id)
    {
        return Cache::remember("brands.{$id}", 60 * 60, function () use ($id) {
            return Brand::findOrFail($id);
        });
    }

    public function searchBrands($query)
    {
        $cacheKey = "brands.search.{$query}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($query) {
            return Brand::where('name', 'like', "%$query%")->get();
        });
    }

    public function updateBrand($id, array $data)
    {
        $updated = Brand::where('id', $id)->update($data);

        Cache::forget('brands.all');
        Cache::forget("brands.{$id}");

        return $updated;
    }

    public function deleteBrand($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        Cache::forget('brands.all');
        Cache::forget("brands.{$id}");
    }
}
