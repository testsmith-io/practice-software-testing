<?php

namespace tests\Feature;

use App\Http\Controllers\UserController;
use App\Mail\ForgetPassword;
use App\Mail\Register;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
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

    public function testUserCreation()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'Street 1',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'postcode' => '1234AA',
            'phone' => '0987654321',
            'dob' => '1970-01-01',
            'email' => 'john@doe.example',
            'password' => 'Test3r01!'
        ];

        $response = $this->postJson('/users/register', $userData);

        $response->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJson([
                'first_name' => 'John',
                'last_name' => 'Doe'
            ]);
    }

    public function testEmailSentInLocalEnvironment()
    {
        $this->app['env'] = 'local';

        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'Street 1',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'postcode' => '1234AA',
            'phone' => '0987654321',
            'dob' => '1970-01-01',
            'email' => 'john@doe.example',
            'password' => 'Test3r01!'
        ];

        $this->postJson('/users/register', $userData);

        Mail::assertSent(Register::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
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

    public function testDisabledAccount()
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

    public function testAuthenticatedUserCanRetrieveTheirInformation()
    {
        $response = $this->getJson('/users/me', $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_OK);

        $response->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }

    public function testUserCanLogoutSuccessfully()
    {
        $response = $this->getJson('/users/logout', $this->headers($this->user));
        $response->assertStatus(ResponseAlias::HTTP_OK);

        $response->assertJson(['message' => 'Successfully logged out']);
        JWTAuth::fromUser($this->user);
        $this->assertFalse(JWTAuth::check());
    }

    public function testUserCanRefreshToken()
    {
        // Create a user
        $user = User::factory()->create();

        // Generate a token for the user
        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', "Bearer {$token}");
        $response = $this->getJson('/users/refresh');

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            'access_token',
        ]);

        $newToken = $response->json('access_token');
        $this->assertNotEquals($token, $newToken);
    }

    public function testAdminUserCanRetrieveAnyUserInfo()
    {
        // Create an admin user
        $adminUser = User::factory()->create(['role' => 'admin']);

        // Create another user
        $otherUser = User::factory()->create();

        $response = $this->getJson("/users/{$otherUser->id}", $this->headers($adminUser));

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson([
                'id' => $otherUser->id,
            ]);
    }

    public function testNonAdminUserCanRetrieveOwnInfo()
    {
        $response = $this->getJson("/users/{$this->user->id}", $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson([
                'id' => $this->user->id,
            ]);
    }

    public function testNonAdminUserCannotRetrieveOtherUserInfo()
    {
        // Create another user
        $otherUser = User::factory()->create();

        $response = $this->getJson("/users/{$otherUser->id}", $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    }

    public function testUserSearchByFirstName()
    {
        // Create users
        User::factory()->create(['first_name' => 'John', 'role' => 'user']);
        User::factory()->create(['first_name' => 'Jane', 'role' => 'user']);

        // Make a GET request to search users by first name
        $response = $this->getJson('/users/search?q=John', $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_OK);

        // Assert the response contains the user with first name 'John'
        $response->assertJsonFragment(['first_name' => 'John']);

        // Assert the response does not contain the user with first name 'Jane'
        $response->assertJsonMissing(['first_name' => 'Jane']);
    }

    public function testUserSearchByCity()
    {
        // Create users
        User::factory()->create(['city' => 'New York', 'role' => 'user']);
        User::factory()->create(['city' => 'Los Angeles', 'role' => 'user']);

        // Make a GET request to search users by city
        $response = $this->getJson('/users/search?q=New York', $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_OK);

        // Assert the response contains the user with city 'New York'
        $response->assertJsonFragment(['city' => 'New York']);

        // Assert the response does not contain the user with city 'Los Angeles'
        $response->assertJsonMissing(['city' => 'Los Angeles']);
    }

    public function testUserCanUpdateOwnInformation()
    {
        $newData = [
            'first_name' => 'UpdatedName',
            'last_name' => 'Doe',
            'address' => 'Street 1',
            'city' => 'City',
            'country' => 'Country',
            'email' => 'john@doe.example',
        ];

        // Make a PUT request to update user information
        $response = $this->putJson("/users/{$this->user->id}", $newData, $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson(['success' => true]);

        // Assert the user's information is updated in the database
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'UpdatedName'
        ]);
    }

    public function testAdminCanUpdateAnyUserInformation()
    {
        // Create an admin user
        $adminUser = User::factory()->create(['role' => 'admin']);

        $newData = [
            'first_name' => 'UpdatedByAdmin',
            'last_name' => 'Doe',
            'address' => 'Street 1',
            'city' => 'City',
            'country' => 'Country',
            'email' => 'john@doe.example',
        ];

        // Make a PUT request to update the other user's information
        $response = $this->putJson("/users/{$this->user->id}", $newData, $this->headers($adminUser));

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson(['success' => true]);

        // Assert the other user's information is updated in the database
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'UpdatedByAdmin'
        ]);
    }

    public function testUserCannotUpdateAnotherUsersInformation()
    {
        $newData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'Street 1',
            'city' => 'City',
            'country' => 'Country',
            'email' => 'john@doe.example',
        ];
        // Create two users
        $otherUser = User::factory()->create();

        // Make a PUT request to attempt to update the other user's information
        $response = $this->putJson("/users/{$otherUser->id}", $newData, $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_FORBIDDEN);
    }

    public function testAdminCanDeleteUser()
    {
        // Create an admin user
        $adminUser = User::factory()->create(['role' => 'admin']);

        // Make a DELETE request to delete the user
        $response = $this->json('DELETE', "/users/{$this->user->id}", [], $this->headers($adminUser));

        $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);

        // Assert the user is deleted from the database
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function testNonAdminCannotDeleteUser()
    {
        // Create two users
        $otherUser = User::factory()->create();

        // Make a DELETE request to attempt to delete the other user
        $response = $this->json('DELETE', "/users/{$otherUser->id}", [], $this->headers($this->user));

        // Assert the response status is 403 Forbidden
        $response->assertStatus(ResponseAlias::HTTP_FORBIDDEN)
            ->assertJson([
                'message' => 'Forbidden'
            ]);
    }

    public function testDeletionPreventedWhenUserIsInUse()
    {
        // Create an admin user
        $adminUser = User::factory()->create(['role' => 'admin']);

        $product = $this->addProduct();

        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);

        // Simulate QueryException by catching and handling it
        $response = $this->json('DELETE', "/users/{$this->user->id}", [], $this->headers($adminUser));

        $response->assertStatus(ResponseAlias::HTTP_CONFLICT)
            ->assertJson([
                'success' => false,
                'message' => 'Seems like this customer is used elsewhere.'
            ]);
    }

    public function testCurrentPasswordIncorrect()
    {
        $response = $this->postJson('/users/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword'
        ], $this->headers($this->user));

        $response->assertStatus(ResponseAlias::HTTP_BAD_REQUEST)
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

        $response->assertStatus(ResponseAlias::HTTP_BAD_REQUEST)
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

        $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function testPasswordChangeSuccess()
    {
        $response = $this->postJson('/users/change-password', [
            'current_password' => 'welcome01',
            'new_password' => 'Test3r01!',
            'new_password_confirmation' => 'Test3r01!'
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

        $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function testRetrieveUsers()
    {
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
