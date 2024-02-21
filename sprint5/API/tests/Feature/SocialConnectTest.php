<?php

namespace tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Tests\TestCase;

class SocialConnectTest extends TestCase
{
    use DatabaseMigrations;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testItRedirectsToCorrectAuthUrlForGoogle()
    {
        // Arrange: Mock the SocialConnect service
        $mockService = Mockery::mock('overload:\SocialConnect\Auth\Service');
        $mockProvider = Mockery::mock('overload:\SocialConnect\Provider\OAuth2\Google');
        $mockProvider->shouldReceive('makeAuthUrl')->andReturn('http://mocked-google-auth-url.com');
        $mockService->shouldReceive('getProvider')->with('google')->andReturn($mockProvider);

        // Act: Call the getAuthUrl method
        $response = $this->get('/auth/social-login?provider=google');

        // Assert: Check if the response redirects to the correct URL
        $response->assertRedirect('http://mocked-google-auth-url.com');
    }

}
