<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    public function testRetrieveUsers()
    {
        User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->get('/users', $this->headers($admin));

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'address',
                        'city',
                        'state',
                        'country',
                        'postcode',
                        'phone',
                        'dob',
                        'email',
                        'created_at'
                    ]
                ]
            ]);
    }

    public function testUserCannotUpdateProfileWithNonNumericPhone()
    {
        $user = User::factory()->create();

        $response = $this->putJson('/users/' . $user->id, [
            'first_name' => 'UpdatedName',
            'last_name' => 'Doe',
            'address' => 'Street 1',
            'city' => 'City',
            'country' => 'Country',
            'phone' => 'Test',
            'email' => 'john@doe.example',
        ], $this->headers($user));

        $response
            ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['phone']);
    }

    public function testUserCanUpdateProfileWithValidPhone()
    {
        $user = User::factory()->create();

        $response = $this->putJson('/users/' . $user->id, [
            'first_name' => 'UpdatedName',
            'last_name' => 'Doe',
            'address' => 'Street 1',
            'city' => 'City',
            'country' => 'Country',
            'phone' => '+1 (555) 123-4567',
            'email' => 'john@doe.example',
        ], $this->headers($user));

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertExactJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '+1 (555) 123-4567',
        ]);
    }

}
