<?php

use App\Models\User;

class UserTest extends TestCase
{
    public function testRequiredFieldsRegisterUser()
    {

        $response = $this->post('/users/register');

        $response
            ->seeStatusCode(422)
            ->seeJson([
                'first_name' => ['The first name field is required.'],
                'last_name' => ['The last name field is required.'],
                'address' => ['The address field is required.'],
                'city' => ['The city field is required.'],
                'country' => ['The country field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ]);
    }


    public function testRegisterUser()
    {
        $payload = ['first_name' => 'new',
            'last_name' => 'new',
            'address' => 'new',
            'city' => 'new',
            'country' => 'new',
            'email' => 'new@test.com',
            'dob' => '2000-01-01',
            'password' => 'Test1234'];

        $response = $this->post('/users/register', $payload);

        $response->seeStatusCode(201)
            ->seeJsonStructure([
                'first_name'
            ]);
    }

    public function testLogin()
    {
        $user = User::factory()->create();

        $payload = ['email' => $user->email,
            'password' => 'welcome123!'];

        $response = $this->post('/users/login', $payload);

        $response
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'access_token',
                'token_type'
            ]);
    }

}
