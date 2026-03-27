<?php

use App\Http\Controllers\UserController;
use App\Mail\ForgetPassword;
use App\Mail\Register;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;


uses(DatabaseMigrations::class);

//covers(UserController::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => bcrypt($password = 'welcome01'), // NOSONAR
    ]);

    Mail::fake();
});

test('user creation', function () {
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => [
            'street' => 'Street 1',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => '1234AA'
        ],
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
});

test('email sent in local environment', function () {
    $this->app['env'] = 'local';

    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => [
            'street' => 'Street 1',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => '1234AA'
        ],
        'phone' => '0987654321',
        'dob' => '1970-01-01',
        'email' => 'john@doe.example',
        'password' => 'Test3r01!'
    ];

    $this->postJson('/users/register', $userData);

    Mail::assertSent(Register::class, function ($mail) use ($userData) {
        return $mail->hasTo($userData['email']);
    });
});

test('successful login', function () {
    $response = $this->post('/users/login', [
        'email' => $this->user->email,
        'password' => 'welcome01', // NOSONAR
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
});

test('failed login', function () {
    $response = $this->post('/users/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
});

test('locked account', function () {
    // Manually lock the account by setting failed login attempts
    $this->user->failed_login_attempts = UserController::MAX_LOGIN_ATTEMPTS;
    $this->user->save();

    $response = $this->post('/users/login', [
        'email' => $this->user->email,
        'password' => 'welcome01', // NOSONAR
    ]);

    $response->assertStatus(ResponseAlias::HTTP_LOCKED);
});

test('disabled account', function () {
    // Disable the account
    $this->user->enabled = false;
    $this->user->save();

    $response = $this->post('/users/login', [
        'email' => $this->user->email,
        'password' => 'welcome01', // NOSONAR
    ]);

    $response->assertStatus(ResponseAlias::HTTP_FORBIDDEN);
});

test('authenticated user can retrieve their information', function () {
    $response = $this->getJson('/users/me', $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_OK);

    $response->assertJson([
        'id' => $this->user->id,
        'email' => $this->user->email,
    ]);
});

test('user can logout successfully', function () {
    $response = $this->getJson('/users/logout', $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_OK);

    $response->assertJson(['message' => 'Successfully logged out']);

    JWTAuth::fromUser($this->user);
    expect(JWTAuth::check())->toBeFalse();
});

test('user can refresh token', function () {
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
});

test('admin user can retrieve any user info', function () {
    // Create an admin user
    $adminUser = User::factory()->create(['role' => 'admin']);

    // Create another user
    $otherUser = User::factory()->create();

    $response = $this->getJson("/users/{$otherUser->id}", $this->headers($adminUser));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJson([
            'id' => $otherUser->id,
        ]);
});

test('non admin user can retrieve own info', function () {
    $response = $this->getJson("/users/{$this->user->id}", $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJson([
            'id' => $this->user->id,
        ]);
});

test('non admin user cannot retrieve other user info', function () {
    // Create another user
    $otherUser = User::factory()->create();

    $response = $this->getJson("/users/{$otherUser->id}", $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
});

test('user search by first name', function () {
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
});

test('user search by city', function () {
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
});

test('user can update own information', function () {
    $newData = [
        'first_name' => 'UpdatedName',
        'last_name' => 'Doe',
        'address' => [
            'street' => 'Street 1',
            'city' => 'City',
            'country' => 'Country'
        ],
        'email' => 'john@doe.example',
    ];

    // Make a PUT request to update user information
    $response = $this->putJson("/users/{$this->user->id}", $newData, $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson(['success' => true]);

    // Assert the user's information is updated in the database
    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'UpdatedName'
    ]);
});

test('user can partial update own information', function () {
    $payload = [
        'first_name' => 'Updated User Name',
        'email' => 'updatedemail@example.com'
    ];

    // Make a PUT request to update user information
    $response = $this->patchJson("/users/{$this->user->id}", $payload, $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson(['success' => true]);

    // Assert the user's information is updated in the database
    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'Updated User Name'
    ]);
});

test('admin can update any user information', function () {
    // Create an admin user
    $adminUser = User::factory()->create(['role' => 'admin']);

    $newData = [
        'first_name' => 'UpdatedByAdmin',
        'last_name' => 'Doe',
        'address' => [
            'street' => 'Street 1',
            'city' => 'City',
            'country' => 'Country'
        ],
        'email' => 'john@doe.example',
    ];

    // Make a PUT request to update the other user's information
    $response = $this->putJson("/users/{$this->user->id}", $newData, $this->headers($adminUser));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson(['success' => true]);

    // Assert the other user's information is updated in the database
    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'UpdatedByAdmin'
    ]);
});

test('user cannot update another users information', function () {
    $newData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => [
            'street' => 'Street 1',
            'city' => 'City',
            'country' => 'Country'
        ],
        'email' => 'john@doe.example',
    ];

    // Create two users
    $otherUser = User::factory()->create();

    // Make a PUT request to attempt to update the other user's information
    $response = $this->putJson("/users/{$otherUser->id}", $newData, $this->headers($this->user));

    //        dd($response);
    $response->assertStatus(ResponseAlias::HTTP_FORBIDDEN);
});

test('admin can delete user', function () {
    // Create an admin user
    $adminUser = User::factory()->create(['role' => 'admin']);

    // Make a DELETE request to delete the user
    $response = $this->json('DELETE', "/users/{$this->user->id}", [], $this->headers($adminUser));

    $response->assertStatus(ResponseAlias::HTTP_NO_CONTENT);

    // Assert the user is deleted from the database
    $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
});

test('non admin cannot delete user', function () {
    // Create two users
    $otherUser = User::factory()->create();

    // Make a DELETE request to attempt to delete the other user
    $response = $this->json('DELETE', "/users/{$otherUser->id}", [], $this->headers($this->user));

    // Assert the response status is 403 Forbidden
    $response->assertStatus(ResponseAlias::HTTP_FORBIDDEN)
        ->assertJson([
            'message' => 'Forbidden'
        ]);
});

test('deletion prevented when user is in use', function () {
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
        ->assertExactJson([
            'success' => false,
            'message' => 'Seems like this customer is used elsewhere.'
        ]);
});

test('current password incorrect', function () {
    $response = $this->postJson('/users/change-password', [
        'current_password' => 'wrongpassword',
        'new_password' => 'newpassword',
        'new_password_confirmation' => 'newpassword'
    ], $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_BAD_REQUEST)
        ->assertExactJson([
            'success' => false,
            'message' => 'Your current password does not matches with the password.',
        ]);
});

test('new password same as current', function () {
    $response = $this->postJson('/users/change-password', [
        'current_password' => 'welcome01', // NOSONAR
        'new_password' => 'welcome01', // NOSONAR
        'new_password_confirmation' => 'welcome01' // NOSONAR
    ], $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_BAD_REQUEST)
        ->assertExactJson([
            'success' => false,
            'message' => 'New Password cannot be same as your current password.'
        ]);
});

test('new password validation failure', function () {
    // Test with a new password that is too short
    $response = $this->postJson('/users/change-password', [
        'current_password' => 'welcome01', // NOSONAR
        'new_password' => 'short',
        'new_password_confirmation' => 'short'
    ], $this->headers($this->user));

    $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['new_password']);
});

test('password change success', function () {
    $response = $this->postJson('/users/change-password', [
        'current_password' => 'welcome01', // NOSONAR
        'new_password' => 'Test3r01!',
        'new_password_confirmation' => 'Test3r01!'
    ], $this->headers($this->user));

    $response->assertOk()
        ->assertExactJson(['success' => true]);
});

test('password reset in local environment', function () {
    $this->app['env'] = 'local';

    $response = $this->postJson('/users/forgot-password', [
        'email' => $this->user->email,
    ]);

    $response->assertOk()
        ->assertExactJson(['success' => true]);

    Mail::assertSent(ForgetPassword::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
});

test('password reset in non local environment', function () {
    $this->app['env'] = 'testing';

    $response = $this->postJson('/users/forgot-password', [
        'email' => $this->user->email,
    ]);

    $response->assertOk()
        ->assertExactJson(['success' => true]);

    Mail::assertNotSent(ForgetPassword::class);
});

test('email does not exist', function () {
    $response = $this->postJson('/users/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});

test('retrieve users', function () {
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
                    'address' => [
                        'street',
                        'city',
                        'state',
                        'country',
                        'postal_code'
                    ],
                    'phone',
                    'dob',
                    'email',
                    'role',
                    'created_at'
                ]
            ]
        ]);
});
