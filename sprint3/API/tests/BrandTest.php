<?php

use App\Models\Brand;
use App\Models\User;

class BrandTest extends TestCase
{

    public function testShowBrands()
    {
        Brand::factory()->create();

        $response = $this->get('/brands');

        $response
            ->seeStatusCode(200)
            ->seeJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }

    public function testShowBrand()
    {
        $brand = Brand::factory()->create();

        $response = $this->get('/brands/' . $brand->id);

        $response
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'name',
                'slug'
            ]);
    }

    public function testAddBrand()
    {
        $payload = ['name' => 'new',
            'slug' => 'some description'];

        $response = $this->post('/brands', $payload);

        $response
            ->seeStatusCode(201)
            ->seeJsonStructure([
                'id',
                'name',
                'slug'
            ]);
    }

    public function testAddBrandRequiredFields()
    {
        $response = $this->post('/brands');

        $response
            ->seeStatusCode(422)
            ->seeJson([
                'name' => ['The name field is required.'],
                'slug' => ['The slug field is required.']
            ]);
    }

    public function testDeleteBrandUnauthorized()
    {
        $brand = Brand::factory()->create();

        $this->json('DELETE','/brands/' . $brand->id)
            ->seeStatusCode(401);
    }

    public function testDeleteBrand()
    {
        $this->refreshApplication();

        $admin = User::factory()->create(['role' => 'admin']);

        $brand = Brand::factory()->create();

        $this->json('DELETE','/brands/' . $brand->id, [], $this->headers($admin))
            ->seeStatusCode(204);
    }

    public function testDeleteNonExistingBrand()
    {
        $this->refreshApplication();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->delete('/brands/99', [], $this->headers($admin))
            ->seeStatusCode(422)
            ->seeJson([
                'id' => ['The selected id is invalid.']
            ]);
    }

    public function testUpdateBrand()
    {
        $brand = Brand::factory()->create();

        $payload = ['name' => 'new name'];

        $this->put('/brands/' . $brand->id, $payload)
            ->seeStatusCode(200)
            ->seeJson([
                'success' => true
            ]);
    }

}
