<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ChefProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_select_chef_role_and_fetch_me(): void
    {
        $login = $this->postJson('/api/wechat/login', ['code' => 'dev-chef-code'])
            ->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('data.user.openid', 'dev_chef_code')
            ->assertJsonPath('data.user.role', null)
            ->assertJsonPath('data.user.phone', null)
            ->json('data');

        $this->assertNotEmpty($login['token']);

        $this->withHeader('Authorization', 'Bearer '.$login['token'])
            ->putJson('/api/me/profile', [
                'nickname' => '张师傅',
                'avatarUrl' => 'https://example.test/avatar.jpg',
                'phone' => '13800000000',
            ])
            ->assertOk()
            ->assertJsonPath('data.nickname', '张师傅')
            ->assertJsonPath('data.avatarUrl', 'https://example.test/avatar.jpg')
            ->assertJsonPath('data.phone', '13800000000');

        $this->withHeader('Authorization', 'Bearer '.$login['token'])
            ->postJson('/api/me/role', ['role' => 'chef'])
            ->assertOk()
            ->assertJsonPath('data.role', 'chef');

        $this->assertTrue(ChefProfile::where('user_id', $login['user']['id'])->exists());

        $this->withHeader('Authorization', 'Bearer '.$login['token'])
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.openid', 'dev_chef_code')
            ->assertJsonPath('data.role', 'chef');
    }

    public function test_role_selection_rejects_invalid_roles(): void
    {
        $user = User::create([
            'openid' => 'dev_diner_code',
            'role' => UserRole::Diner,
            'api_token' => 'plain-token',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$user->api_token)
            ->postJson('/api/me/role', ['role' => 'admin'])
            ->assertStatus(422);
    }
}
