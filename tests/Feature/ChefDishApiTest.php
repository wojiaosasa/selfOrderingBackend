<?php

namespace Tests\Feature;

use App\Enums\DishCategory;
use App\Enums\DishStatus;
use App\Enums\UserRole;
use App\Models\ChefProfile;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChefDishApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_chef_can_update_profile_and_manage_dishes(): void
    {
        $chef = $this->createChef('chef-token');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->getJson('/api/chef/profile')
            ->assertOk()
            ->assertJsonPath('data.displayName', '张师傅')
            ->assertJsonPath('data.avatarUrl', 'https://example.test/avatar.jpg')
            ->assertJsonPath('data.bindingCode', 'CHEF1234');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->putJson('/api/chef/profile', [
                'displayName' => '家宴厨房',
                'bio' => '周末家常菜',
                'serviceNote' => '提前一天预约',
                'isAcceptingOrders' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.displayName', '家宴厨房')
            ->assertJsonPath('data.isAcceptingOrders', false);

        $dishId = $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->postJson('/api/chef/dishes', [
                'name' => '红烧肉',
                'imageUrl' => 'https://example.test/hongshaorou.jpg',
                'category' => 'meat',
                'recipe' => '小火慢炖',
                'tasteNote' => '偏甜',
                'price' => '38',
                'portionNote' => '2-3人份',
                'sortOrder' => 10,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', '红烧肉')
            ->assertJsonPath('data.recipe', '小火慢炖')
            ->assertJsonPath('data.tasteNote', '偏甜')
            ->assertJsonPath('data.status', 'active')
            ->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->getJson('/api/chef/dishes')
            ->assertOk()
            ->assertJsonPath('data.0.id', $dishId)
            ->assertJsonPath('data.0.name', '红烧肉');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->putJson('/api/chef/dishes/'.$dishId, [
                'name' => '红烧肉套餐',
                'category' => 'meat',
                'price' => '42',
                'sortOrder' => 8,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '红烧肉套餐')
            ->assertJsonPath('data.price', '42');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->patchJson('/api/chef/dishes/'.$dishId.'/status', ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->deleteJson('/api/chef/dishes/'.$dishId)
            ->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertSoftDeleted('dishes', ['id' => $dishId]);
    }

    public function test_chef_cannot_manage_another_chefs_dish(): void
    {
        $owner = $this->createChef('owner-token', 'OWNER001');
        $otherChef = $this->createChef('other-token', 'OTHER001');

        $dish = Dish::create([
            'chef_id' => $owner->id,
            'name' => '清炒时蔬',
            'category' => DishCategory::Vegetable,
            'status' => DishStatus::Active,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$otherChef->api_token)
            ->putJson('/api/chef/dishes/'.$dish->id, [
                'name' => '不能改',
                'category' => 'vegetable',
            ])
            ->assertForbidden();
    }

    private function createChef(string $token, string $bindingCode = 'CHEF1234'): User
    {
        $chef = User::create([
            'openid' => $token.'-openid',
            'role' => UserRole::Chef,
            'nickname' => '张师傅',
            'avatar_url' => 'https://example.test/avatar.jpg',
            'api_token' => $token,
        ]);

        ChefProfile::create([
            'user_id' => $chef->id,
            'display_name' => '张师傅',
            'binding_code' => $bindingCode,
        ]);

        return $chef;
    }
}
