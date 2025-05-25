<?php

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

//covers(FavoriteController::class);

test('retrieve favorites', function () {
    $user = User::factory()->create();

    addFavorite($user);

    $response = $this->json('get', '/favorites', [], $this->headers($user));

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'user_id',
                'product_id',
                'product'
            ]
        ]);
});

test('retrieve favorite', function () {
    $user = User::factory()->create();

    $favorite = addFavorite($user);

    $response = $this->json('get', "/favorites/{$favorite->id}", [], $this->headers($user));

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'user_id',
            'product_id',
        ]);
});

test('delete favorite', function () {
    $user = User::factory()->create();

    $favorite = addFavorite($user);

    $response = $this->json('delete', "/favorites/{$favorite->id}", [], $this->headers($user));

    $response
        ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
});

test('guests cannot delete favorites', function () {
    $user = User::factory()->create();

    $favorite = addFavorite($user);

    $this->deleteJson("/favorites/{$favorite->id}")
        ->assertUnauthorized(); // Or ->assertStatus(401);
});

test('add favorite', function () {
    $user = User::factory()->create();

    $product = $this->addProduct();

    $payload = [
        'product_id' => $product->id
    ];

    $response = $this->json('post', '/favorites', $payload, $this->headers($user));

    $response
        ->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure([
            'product_id',
            'user_id',
            'id'
        ]);
});

/**
 * @param Model|Collection $user
 * @return Collection|Model
 */
function addFavorite(Model|Collection $user): Collection|Model
{
    $product = addProduct();

    $favorite = Favorite::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id
    ]);
    return $favorite;
}
