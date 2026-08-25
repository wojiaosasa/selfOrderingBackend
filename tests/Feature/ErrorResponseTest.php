<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_errors_use_api_response_shape(): void
    {
        $user = User::create([
            'openid' => 'diner-openid',
            'role' => UserRole::Diner,
            'api_token' => 'plain-token',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$user->api_token)
            ->postJson('/api/me/role', ['role' => 'admin'])
            ->assertStatus(422)
            ->assertJsonPath('code', 422)
            ->assertJsonPath('message', 'The given data was invalid.')
            ->assertJsonPath('data', null);
    }

    public function test_unauthenticated_errors_use_api_response_shape(): void
    {
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJsonPath('code', 401)
            ->assertJsonPath('message', 'Unauthenticated.')
            ->assertJsonPath('data', null);
    }
}
