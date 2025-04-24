<?php

use App\Models\Cart;
use App\Models\CartItem;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

covers(\App\Http\Controllers\CartController::class);

test('create cart', function () {
    $response = $this->postJson('/carts', ['lat' => 40.7128, 'lng' => -74.0060]);

    $response->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure(['id']);
});

test('create cart without coordinates', function () {
    $response = $this->postJson('/carts', []);

    $response->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure(['id']);
});

test('add new item to cart', function () {
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
});

test('update item quantity in cart', function () {
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
});

test('cart not found', function () {
    $nonExistentCartId = 999;
    $product = $this->addProduct();

    $response = $this->postJson("/carts/{$nonExistentCartId}", [
        'product_id' => $product->id,
        'quantity' => 1
    ]);

    $response->assertStatus(404);
});

test('add product to cart', function () {
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
});

test('add product to cart no discount location', function () {
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
});

test('combined discount', function () {
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

    expect($cart->additional_discount_percentage)->toEqual(15);
});

test('retrieve specific cart', function () {
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

    expect($responseData['id'])->toEqual($cart->id);

    // Check if the discounted price is calculated correctly
    $expectedDiscountedPrice = round($product->price * 0.9, 2);
    // 10% discount
    expect($responseData['cart_items'][0]['discounted_price'])->toEqual($expectedDiscountedPrice);
});

test('get cart not found', function () {
    $nonExistentCartId = 999;

    $response = $this->getJson("/carts/{$nonExistentCartId}");

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    $response->assertJson(['message' => 'Requested item not found']);
});

test('successful quantity update', function () {
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
});

test('update cart not found', function () {
    $nonExistentCartId = 999;
    $product = $this->addProduct();

    $response = $this->putJson("/carts/{$nonExistentCartId}/product/quantity", [
        'product_id' => $product->id,
        'quantity' => 1
    ]);

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    $response->assertJson(['message' => 'Cart doesn\'t exist']);
});

test('successful cart deletion', function () {
    $cart = Cart::factory()->create();

    $response = $this->deleteJson("/carts/{$cart->id}");

    $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
});

test('delete cart not found', function () {
    $nonExistentCartId = 999;

    $response = $this->deleteJson("/carts/{$nonExistentCartId}");

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    $response->assertJson(['message' => 'Cart doesnt exists']);
});

test('deletion when cart is in use', function () {
    $cart = Cart::factory()->create();
    $product = $this->addProduct();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1
    ]);

    $response = $this->deleteJson("/carts/{$cart->id}");

    $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
});

test('successful product removal from cart', function () {
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
});

test('delete product cart not found', function () {
    $nonExistentCartId = 999;
    $productId = 1;

    $response = $this->deleteJson("/carts/{$nonExistentCartId}/product/{$productId}");

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    $response->assertJson(['message' => 'Cart doesnt exists']);
});

test('product not found in cart', function () {
    $cart = Cart::factory()->create();
    $nonExistentProductId = 999;

    $response = $this->deleteJson("/carts/{$cart->id}/product/{$nonExistentProductId}");

    $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
});
