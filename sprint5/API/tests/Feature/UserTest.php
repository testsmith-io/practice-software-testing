<?php

namespace tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class UserTest extends TestCase {
    use DatabaseMigrations;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => bcrypt($password = 'welcome01'),
        ]);
    }

    public function test_successful_login()
    {
        $response = $this->post('/users/login', [
            'email' => $this->user->email,
            'password' => 'welcome01',
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
    }

    public function test_failed_login()
    {
        $response = $this->post('/users/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
    }

    public function test_locked_account()
    {
        // Manually lock the account by setting failed login attempts
        $this->user->failed_login_attempts = UserController::MAX_LOGIN_ATTEMPTS;
        $this->user->save();

        $response = $this->post('/users/login', [
            'email' => $this->user->email,
            'password' => 'welcome01',
        ]);

        $response->assertStatus(ResponseAlias::HTTP_LOCKED);
    }

    public function test_disabled_account()
    {
        // Disable the account
        $this->user->enabled = false;
        $this->user->save();

        $response = $this->post('/users/login', [
            'email' => $this->user->email,
            'password' => 'welcome01',
        ]);

        $response->assertStatus(ResponseAlias::HTTP_FORBIDDEN);
    }

    public function testRetrieveUsers() {
        User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->getJson('/users', $this->headers($admin));

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
