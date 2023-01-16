<?php

use App\Models\Category;
use App\Models\User;

class CategoryTest extends TestCase
{
    public function testShowCategories()
    {
        Category::factory()->create();

        $response = $this->get('/categories');

        $response
            ->seeStatusCode(200)
            ->seeJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }

    public function testShowCategory()
    {
        $category = Category::factory()->create();

        $response = $this->get('/categories/' . $category->id);

        $response
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'name',
                'slug'
            ]);
    }

    public function testAddCategory()
    {
        $payload = ['name' => 'new',
            'slug' => 'some description'];

        $response = $this->post('/categories', $payload);

        $response
            ->seeStatusCode(201)
            ->seeJsonStructure([
                'id',
                'name',
                'slug'
            ]);
    }

    public function testAddCategoryRequiredFields()
    {
        $response = $this->post('/categories');

        $response
            ->seeStatusCode(422)
            ->seeJson([
                'name' => ['The name field is required.'],
                'slug' => ['The slug field is required.']
            ]);
    }

    public function testDeleteCategoryUnauthorized()
    {
        $brand = Category::factory()->create();

        $this->json('DELETE','/categories/' . $brand->id)
            ->seeStatusCode(401);
    }

    public function testDeleteCategory()
    {
        $this->refreshApplication();

        $admin = User::factory()->create(['role' => 'admin']);

        $category = Category::factory()->create();

        $this->delete('/categories/' . $category->id, [], $this->headers($admin))
            ->seeStatusCode(204);
    }

    public function testDeleteNonExistingCategory()
    {
        $this->refreshApplication();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->delete('/categories/99', [], $this->headers($admin))
            ->seeStatusCode(422)
            ->seeJson([
                'id' => ['The selected id is invalid.']
            ]);
    }

    public function testUpdateCategory()
    {
        $category = Category::factory()->create();

        $payload = ['name' => 'new name'];

        $response = $this->put('/categories/' . $category->id, $payload);

        $response
            ->seeStatusCode(200)
            ->seeJson([
                'success' => true
            ]);
    }

}
