<?php

use App\Mail\Contact;
use \Faker\Generator;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('send message as guest', function () {
    $response = addMessage($this, $this->faker);

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);
});

test('send message as logged in user', function () {
    $user = User::factory()->create();

    $payload = [
        'first_name' => '',
        'last_name' => '',
        'email' => 'test@example.com',
        'subject' => 'Return',
        'message' => $this->faker->text(55)
    ];

    $response = $this->json('post', '/messages', $payload, $this->headers($user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'user_id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);
});

test('attach file not empty', function () {
    $response = addMessage($this, $this->faker);

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);

    $response = $this->postJson("/messages/{$response->json('id')}/attach-file", [
        'file' => UploadedFile::fake()->create('log.txt', 500)
    ]);
    $response->assertJson([
        'errors' => ['Currently we only allow empty files.']
    ]);
});

test('attach empty file', function () {
    $response = addMessage($this, $this->faker);

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);

    $response = $this->postJson("/messages/{$response->json('id')}/attach-file", [
        'file' => UploadedFile::fake()->create('log.txt', 0)
    ]);
    $response->assertJson([
        'success' => 'true'
    ]);
});

test('attach file wrong extension', function () {
    $response = addMessage($this, $this->faker);

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);

    $response = $this->postJson("/messages/{$response->json('id')}/attach-file", [
        'file' => UploadedFile::fake()->create('log.pdf', 0)
    ]);
    $response->assertJson([
        'errors' => ['The file extension is incorrect, we only accept txt files.']
    ]);
});

test('attach without file', function () {
    $response = addMessage($this, $this->faker);

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);

    $response = $this->postJson("/messages/{$response->json('id')}/attach-file", [
    ]);
    $response->assertJson([
        'errors' => ['No file attached.']
    ]);
});

test('retrieve messages as admin', function () {
    $user = User::factory()->create(['role' => 'admin']);

    addMessage($this, $this->faker);

    $response = $this->json('get', '/messages', [], $this->headers($user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'email',
                    'subject',
                    'message',
                    'created_at'
                ]
            ]
        ]);
});

test('retrieve messages as logged in user', function () {
    $user = User::factory()->create();

    $payload = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'subject' => 'Return',
        'message' => $this->faker->text(55)
    ];

    $this->json('post', '/messages', $payload, $this->headers($user));

    $response = $this->json('get', '/messages', [], $this->headers($user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'email',
                    'subject',
                    'message',
                    'created_at'
                ]
            ]
        ]);
});

test('retrieve message as admin', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $message = addMessage($this, $this->faker);

    $response = $this->json('get', "/messages/{$message->json('id')}", [], $this->headers($user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'user_id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);
});

test('retrieve message as logged in user', function () {
    $user = User::factory()->create();

    $payload = [
        'first_name' => '',
        'last_name' => '',
        'email' => 'test@example.com',
        'subject' => 'Return',
        'message' => $this->faker->text(55)
    ];

    $message = $this->json('post', '/messages', $payload, $this->headers($user));

    $response = $this->json('get', "/messages/{$message->json('id')}", [], $this->headers($user));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'user_id',
            'email',
            'subject',
            'message',
            'created_at'
        ]);
});

test('message rely', function () {
    $message = addMessage($this, $this->faker);
    $admin = User::factory()->create(['role' => 'admin']);
    $payload = [
        'message' => 'some reply message'
    ];

    $reply = $this->json('post', "/messages/{$message->json('id')}/reply", $payload, $this->headers($admin));

    $reply->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure([
            'message',
            'created_at'
        ]);
});

test('update status', function () {
    $message = addMessage($this, $this->faker);
    $admin = User::factory()->create(['role' => 'admin']);
    $payload = [
        'status' => 'RESOLVED'
    ];

    $reply = $this->json('put', "/messages/{$message->json('id')}/status", $payload, $this->headers($admin));

    $reply->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'success'
        ]);
});

/**
 * @param \Tests\TestCase $testCase
 * @param Faker\Generator $faker
 * @return \Illuminate\Testing\TestResponse
 */
function addMessage(\Tests\TestCase $testCase, Faker\Generator $faker): \Illuminate\Testing\TestResponse
{
    $payload = [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'subject' => 'Return',
        'message' => $faker->text(55)
    ];

    return $testCase->postJson('/messages', $payload);
}

test('email is sent in local environment', function () {
    Mail::fake();
    $user = User::factory()->create();

    $data = [
        'name' => $user->first_name . ' ' . $user->last_name,
        'subject' => $this->faker->sentence,
        'message' => $this->faker->paragraph
    ];

    $this->app['env'] = 'local';

    $response = $this->postJson('/messages', $data, $this->headers($user));

    Mail::assertSent(Contact::class);
    $this->assertDatabaseHas('contact_requests', ['user_id' => $user->id]);
    $response->assertStatus(200);
});
