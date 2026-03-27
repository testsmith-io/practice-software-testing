<?php

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

}
