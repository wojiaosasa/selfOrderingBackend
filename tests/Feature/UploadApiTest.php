<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_chef_can_upload_dish_image(): void
    {
        Storage::fake('public');

        $chef = User::create([
            'openid' => 'chef-openid',
            'role' => UserRole::Chef,
            'api_token' => 'chef-token',
        ]);

        $url = $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->postJson('/api/uploads/dish-image', [
                'image' => UploadedFile::fake()->create('dish.jpg', 512, 'image/jpeg'),
            ])
            ->assertCreated()
            ->assertJsonPath('code', 0)
            ->json('data.url');

        $this->assertStringStartsWith('/storage/dish-images/', $url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $url));
    }

    public function test_diner_cannot_upload_dish_image(): void
    {
        $diner = User::create([
            'openid' => 'diner-openid',
            'role' => UserRole::Diner,
            'api_token' => 'diner-token',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->postJson('/api/uploads/dish-image', [
                'image' => UploadedFile::fake()->create('dish.jpg', 512, 'image/jpeg'),
            ])
            ->assertForbidden()
            ->assertJsonPath('code', 403)
            ->assertJsonPath('data', null);
    }
}
