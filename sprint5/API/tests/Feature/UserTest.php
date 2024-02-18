<?php

namespace tests\Feature;

use App\Http\Controllers\UserController;
use App\Mail\ForgetPassword;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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

        Mail::fake();
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

    public function testCurrentPasswordIncorrect()
    {
        $response = $this->postJson('/users/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword'
        ], $this->headers($this->user));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Your current password does not matches with the password.',
            ]);
    }

    public function testNewPasswordSameAsCurrent()
    {
        $response = $this->postJson('/users/change-password', [
            'current_password' => 'welcome01',
            'new_password' => 'welcome01',
            'new_password_confirmation' => 'welcome01'
        ], $this->headers($this->user));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'New Password cannot be same as your current password.'
            ]);
    }

    public function testNewPasswordValidationFailure()
    {
        // Test with a new password that is too short
        $response = $this->postJson('/users/change-password', [
            'current_password' => 'welcome01',
            'new_password' => 'short',
            'new_password_confirmation' => 'short'
        ], $this->headers($this->user));

        $response->assertStatus(422) // HTTP 422 Unprocessable Entity
        ->assertJsonValidationErrors(['new_password']);
    }

    public function testPasswordChangeSuccess()
    {
        $response = $this->postJson('/users/change-password', [
            'current_password' => 'welcome01',
            'new_password' => 'newstrongpassword',
            'new_password_confirmation' => 'newstrongpassword'
        ], $this->headers($this->user));

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function testPasswordResetInLocalEnvironment()
    {
        $this->app['env'] = 'local';

        $response = $this->postJson('/users/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertSent(ForgetPassword::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function testPasswordResetInNonLocalEnvironment()
    {
        $this->app['env'] = 'testing';

        $response = $this->postJson('/users/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertNotSent(ForgetPassword::class);
    }

    public function testEmailDoesNotExist()
    {
        $response = $this->postJson('/users/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422) // HTTP 422 Unprocessable Entity
        ->assertJsonValidationErrors(['email']);
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
