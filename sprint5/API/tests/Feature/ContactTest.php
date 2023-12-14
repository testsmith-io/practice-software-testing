<?php

namespace tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class ContactTest extends TestCase {
    use DatabaseMigrations;

    public function testSendMessageAsGuest() {
        $response = $this->addMessage();

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'email',
                'subject',
                'message',
                'created_at'
            ]);
    }

    public function testSendMessageAsLoggedInUser() {
        $user = User::factory()->create();

        $payload = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
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
    }

    public function testAttachFilNotEmpty() {

        $response = $this->addMessage();

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'email',
                'subject',
                'message',
                'created_at'
            ]);

        $response = $this->postJson('/messages/' . $response->json('id') . '/attach-file', [
            'file' => UploadedFile::fake()->create('log.txt', 500)
        ]);
        $response->assertJson([
            'errors' => ['Currently we only allow empty files.']
        ]);
    }

    public function testAttachEmptyFile() {

        $response = $this->addMessage();

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'email',
                'subject',
                'message',
                'created_at'
            ]);

        $response = $this->postJson('/messages/' . $response->json('id') . '/attach-file', [
            'file' => UploadedFile::fake()->create('log.txt', 0)
        ]);
        $response->assertJson([
            'success' => 'true'
        ]);
    }

    public function testAttachWithoutFile() {

        $response = $this->addMessage();

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'email',
                'subject',
                'message',
                'created_at'
            ]);

        $response = $this->postJson('/messages/' . $response->json('id') . '/attach-file', [
        ]);
        $response->assertJson([
            'errors' => ['No file attached.']
        ]);
    }

    public function testRetrieveMessagesAsAdmin() {
        $user = User::factory()->create(['role' => 'admin']);

        $this->addMessage();

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
    }

    public function testRetrieveMessagesAsLoggedInUser() {
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
    }

    public function testRetrieveMessageAsAdmin() {
        $user = User::factory()->create(['role' => 'admin']);

        $message = $this->addMessage();

        $response = $this->json('get', '/messages/' . $message->json('id'), [], $this->headers($user));

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'user_id',
                'email',
                'subject',
                'message',
                'created_at'
            ]);
    }

    public function testRetrieveMessageAsLoggedInUser() {
        $user = User::factory()->create();

        $payload = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'subject' => 'Return',
            'message' => $this->faker->text(55)
        ];

        $message = $this->json('post', '/messages', $payload, $this->headers($user));

        $response = $this->json('get', '/messages/' . $message->json('id'), [], $this->headers($user));

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'user_id',
                'email',
                'subject',
                'message',
                'created_at'
            ]);
    }

    public function testMessageRely() {
        $message = $this->addMessage();
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = [
            'message' => 'some reply message'
        ];

        $reply = $this->json('post', '/messages/' . $message->json('id') . '/reply', $payload, $this->headers($admin));

        $reply->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJsonStructure([
                'message',
                'created_at'
            ]);
    }

    public function testUpdateStatus() {
        $message = $this->addMessage();
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = [
            'status' => 'RESOLVED'
        ];

        $reply = $this->json('put', '/messages/' . $message->json('id') . '/status', $payload, $this->headers($admin));

        $reply->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'success'
            ]);
    }

    /**
     * @return \Illuminate\Testing\TestResponse
     */
    public function addMessage(): \Illuminate\Testing\TestResponse {
        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'subject' => 'Return',
            'message' => $this->faker->text(55)
        ];

        $response = $this->postJson('/messages', $payload);
        return $response;
    }

}
