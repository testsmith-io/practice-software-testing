<?php

namespace tests\Feature;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class FavoriteTest extends TestCase {
    use DatabaseMigrations;

    public function testRetrieveFavorites() {
        $user = User::factory()->create();

        $this->addFavorite($user);

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
    }

    public function testRetrieveFavorite() {

        $user = User::factory()->create();

        $favorite = $this->addFavorite($user);

        $response = $this->json('get', "/favorites/{$favorite->id}", [], $this->headers($user));

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'user_id',
                'product_id',
            ]);
    }

    public function testDeleteFavorite() {

        $user = User::factory()->create();

        $favorite = $this->addFavorite($user);

        $response = $this->json('delete', "/favorites/{$favorite->id}", [], $this->headers($user));

        $response
            ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    }

    public function testAddFavorite() {
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
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection $user
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function addFavorite(\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection $user): \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model {
        $product = $this->addProduct();

        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
        return $favorite;
    }

}
