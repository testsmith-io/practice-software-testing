<?php

use App\Models\Product;
use App\Models\User;

class ProductTest extends TestCase
{
    public function testShowProducts()
    {
        Product::factory()->create();

        $response = $this->get('/products');

        $response
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'price',
                        'name',
                    ]
                ]
            ]);
    }

    public function testShowProduct()
    {
        $product = Product::factory()->create();

        $response = $this->get('/products/' . $product->id);

        $response
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'name',
                'description',
                'price',
                'name',
            ]);
    }

    public function testAddProduct()
    {
        $payload = ['name' => 'new',
            'description' => 'some description',
            'brand_id' => 1,
            'category_id' => 1,
            'price' => 4.99,
            'is_location_offer' => false,
            'is_rental' => false,
            'product_image_id' => 1];

        $response = $this->post('/products', $payload);

        $response
            ->seeStatusCode(201)
            ->seeJsonStructure([
                'id',
                'name',
                'description',
                'price',
                'name',
            ]);
    }

    public function testAddProductRequiredFields()
    {
        $response = $this->post('/products');

        $response
            ->seeStatusCode(422)
            ->seeJson([
                'name' => ['The name field is required.'],
                'price' => ['The price field is required.'],
                'category_id' => ['The category id field is required.'],
                'brand_id' => ['The brand id field is required.']
            ]);
    }

    public function testDeleteProductUnauthorized()
    {
        $brand = Product::factory()->create();

        $this->json('DELETE','/products/' . $brand->id)
            ->seeStatusCode(401);
    }

    public function testDeleteProduct()
    {
        $this->refreshApplication();

        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::factory()->create();

        $this->delete('/products/' . $product->id, [], $this->headers($admin))
            ->seeStatusCode(204);
    }

    public function testDeleteNonExistingProduct()
    {
        $this->refreshApplication();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->delete('/products/99', [], $this->headers($admin))
            ->seeStatusCode(422)
            ->seeJson([
                'id' => ['The selected id is invalid.']
            ]);
    }

    public function testUpdateProduct()
    {
        $product = Product::factory()->create();

        $payload = ['name' => 'new name'];

        $this->put('/products/' . $product->id, $payload)
            ->seeStatusCode(200)
            ->seeJson([
                'success' => true
            ]);
    }

}
