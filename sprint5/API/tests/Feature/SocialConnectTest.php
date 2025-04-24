<?php

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

afterEach(function () {
    Mockery::close();
});

test('it redirects to correct auth url for google', function () {
    // Arrange: Mock the SocialConnect service
    $mockService = Mockery::mock('overload:\SocialConnect\Auth\Service');
    $mockProvider = Mockery::mock('overload:\SocialConnect\Provider\OAuth2\Google');
    $mockProvider->shouldReceive('makeAuthUrl')->andReturn('http://mocked-google-auth-url.com');
    $mockService->shouldReceive('getProvider')->with('google')->andReturn($mockProvider);

    // Act: Call the getAuthUrl method
    $response = $this->get('/auth/social-login?provider=google');

    // Assert: Check if the response redirects to the correct URL
    $response->assertRedirect('http://mocked-google-auth-url.com');
});