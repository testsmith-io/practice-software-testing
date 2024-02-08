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
        $user = User::factory()->create();
        $response = $this->addMessage($user);

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
        $user = User::factory()->create();
        $message = $this->addMessage($user);
        $payload = [
            'message' => 'some reply message'
        ];

        $reply = $this->json('post', '/messages/' . $message->json('id') . '/reply', $payload, $this->headers($user));

        $reply->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJsonStructure([
                'message',
                'created_at'
            ]);
    }

    public function testUpdateStatus() {
        $user = User::factory()->create();
        $message = $this->addMessage($user);
        $payload = [
            'status' => 'RESOLVED'
        ];

        $reply = $this->json('put', '/messages/' . $message->json('id') . '/status', $payload, $this->headers($user));

        $reply->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'success'
            ]);
    }

    /**
     * @return \Illuminate\Testing\TestResponse
     */
    public function addMessage($user): \Illuminate\Testing\TestResponse {
        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'subject' => 'Return',
            'message' => $this->faker->text(55)
        ];

        $response = $this->post('/messages', $payload, [], $this->headers($user));
        return $response;
    }

}
