<?php

namespace tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class CategoryTest extends TestCase {
    use DatabaseMigrations;

    public function testRetrieveCategories() {
        Category::factory()->create();

        $response = $this->get('/categories');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }

    public function testRetrieveTreeOfCategories() {
        Category::factory()->create();

        $response = $this->get('/categories/tree');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }

    public function testRetrieveTreeOfCategoriesBySlug() {
        Category::factory()->create([
            'slug' => 'test'
        ]);

        $response = $this->get('/categories/tree?by_category_slug=test');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }

    public function testRetrieveCategory() {
        $category = Category::factory()->create();

        $response = $this->get('/categories/' . $category->id);

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'name',
                'slug'
            ]);
    }

    public function testAddCategory() {
        $payload = ['name' => 'new',
            'slug' => 'some description'];

        $response = $this->post('/categories', $payload);

        $response
            ->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'name',
                'slug'
            ]);
    }

    public function testAddCategoryRequiredFields() {
        $response = $this->post('/categories');

        $response
            ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'name' => ['The name field is required.'],
                'slug' => ['The slug field is required.']
            ]);
    }

    public function testDeleteCategory() {
        $category = Category::factory()->create();

        $this->delete('/categories/' . $category->id)
            ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    }

    public function testDeleteNonExistingCategory() {
        $this->delete('/categories/99')
            ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'id' => ['The selected id is invalid.']
            ]);
    }

    public function testDeleteCategoryThatIsInUse() {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $productImage = ProductImage::factory()->create();

        Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'product_image_id' => $productImage->id]);


        $this->json('DELETE', '/categories/' . $category->id)
            ->assertStatus(ResponseAlias::HTTP_CONFLICT);
    }

    public function testUpdateCategory() {
        $category = Category::factory()->create();

        $payload = ['name' => 'new name'];

        $response = $this->put('/categories/' . $category->id, $payload);

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson([
                'success' => true
            ]);
    }

    public function testSearchCategory() {
        Category::factory()->create(['name' => 'categoryname']);

        $this->get('/categories/search?q=categoryname')
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }
}
