<?php

namespace tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreateCart()
    {
        $response = $this->postJson('/carts', ['lat' => 40.7128, 'lng' => -74.0060]);

        $response->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJsonStructure(['id']);
    }

    public function testCreateCartWithoutCoordinates()
    {
        $response = $this->postJson('/carts', []);

        $response->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJsonStructure(['id']);
    }

    public function testAddNewItemToCart()
    {
        $cart = Cart::factory()->create();
        $product = $this->addProduct();

        $response = $this->postJson("/carts/{$cart->id}", [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    public function testUpdateItemQuantityInCart()
    {
        $cart = Cart::factory()->create();
        $product = $this->addProduct();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->postJson("/carts/{$cart->id}", [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3
        ]);
    }

    public function testCartNotFound()
    {
        $nonExistentCartId = 999;

        $response = $this->postJson("/carts/{$nonExistentCartId}", [
            'product_id' => 1,
            'quantity' => 1
        ]);

        $response->assertStatus(404);
    }

    public function testAddProductToCart()
    {
        $cart = Cart::factory()->create(['lat' => 40.7128, 'lng' => 74.0060]);
        $product = $this->addProduct();

        $response = $this->postJson("/carts/{$cart->id}", [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    public function testAddProductToCartNoDiscountLocation()
    {
        $cart = Cart::factory()->create(['lat' => 40.7128, 'lng' => -74.0060]);
        $product = $this->addProduct();

        $response = $this->postJson("/carts/{$cart->id}", [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    public function testCombinedDiscount()
    {
        $cart = Cart::factory()->create();
        $product = $this->addProduct();
        $rental = $this->addRental();

        $this->postJson("/carts/{$cart->id}", [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $this->postJson("/carts/{$cart->id}", [
            'product_id' => $rental->id,
            'quantity' => 1
        ]);

        // Reload cart to get updated data
        $cart = $cart->refresh();

        $this->assertEquals(15, $cart->additional_discount_percentage);
    }

    public function testRetrieveSpecificCart()
    {
        $cart = Cart::factory()->create();
        $product = $this->addProduct();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'discount_percentage' => 10
        ]);

        $response = $this->getJson("/carts/{$cart->id}");

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $responseData = $response->json();

        $this->assertEquals($cart->id, $responseData['id']);

        // Check if the discounted price is calculated correctly
        $expectedDiscountedPrice = round($product->price * 0.9, 2); // 10% discount
        $this->assertEquals($expectedDiscountedPrice, $responseData['cart_items'][0]['discounted_price']);
    }

    public function testGetCartNotFound()
    {
        $nonExistentCartId = 999;

        $response = $this->getJson("/carts/{$nonExistentCartId}");

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
        $response->assertJson(['message' => 'Requested item not found']);
    }

    public function testSuccessfulQuantityUpdate()
    {
        $cart = Cart::factory()->create();
        $product = $this->addProduct();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $updatedQuantity = 3;

        $response = $this->putJson("/carts/{$cart->id}/product/quantity", [
            'product_id' => $product->id,
            'quantity' => $updatedQuantity
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $updatedQuantity
        ]);
    }

    public function testUpdateCartNotFound()
    {
        $nonExistentCartId = 999;
        $product = $this->addProduct();

        $response = $this->putJson("/carts/{$nonExistentCartId}/product/quantity", [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
        $response->assertJson(['message' => 'Cart doesnt exists']);
    }

    public function testSuccessfulCartDeletion()
    {
        $cart = Cart::factory()->create();

        $response = $this->deleteJson("/carts/{$cart->id}");

        $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function testDeleteCartNotFound()
    {
        $nonExistentCartId = 999;

        $response = $this->deleteJson("/carts/{$nonExistentCartId}");

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
        $response->assertJson(['message' => 'Cart doesnt exists']);
    }

    public function testDeletionWhenCartIsInUse()
    {
        $cart = Cart::factory()->create();
        $product = $this->addProduct();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->deleteJson("/carts/{$cart->id}");

        $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    }

    public function testSuccessfulProductRemovalFromCart()
    {
        $cart = Cart::factory()->create();
        $product = $this->addProduct();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->deleteJson("/carts/{$cart->id}/product/{$product->id}");

        $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id
        ]);
    }

    public function testDeleteProductCartNotFound()
    {
        $nonExistentCartId = 999;
        $productId = 1;

        $response = $this->deleteJson("/carts/{$nonExistentCartId}/product/{$productId}");

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
        $response->assertJson(['message' => 'Cart doesnt exists']);
    }

    public function testProductNotFoundInCart()
    {
        $cart = Cart::factory()->create();
        $nonExistentProductId = 999;

        $response = $this->deleteJson("/carts/{$cart->id}/product/{$nonExistentProductId}");

        $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    }
}
