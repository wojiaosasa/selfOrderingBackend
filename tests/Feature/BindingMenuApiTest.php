<?php

namespace Tests\Feature;

use App\Enums\DishCategory;
use App\Enums\DishStatus;
use App\Enums\UserRole;
use App\Models\ChefDinerBinding;
use App\Models\ChefProfile;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BindingMenuApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_diner_can_bind_chef_and_view_active_menu(): void
    {
        $chef = $this->createChef();
        $diner = User::create([
            'openid' => 'diner-openid',
            'role' => UserRole::Diner,
            'nickname' => '小王',
            'api_token' => 'diner-token',
        ]);

        $activeDish = Dish::create([
            'chef_id' => $chef->id,
            'name' => '番茄炒蛋',
            'category' => DishCategory::Vegetable,
            'status' => DishStatus::Active,
            'sort_order' => 2,
        ]);

        Dish::create([
            'chef_id' => $chef->id,
            'name' => '今日售罄',
            'category' => DishCategory::Meat,
            'status' => DishStatus::Inactive,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->postJson('/api/diner/bindings', ['bindingCode' => 'CHEF1234'])
            ->assertCreated()
            ->assertJsonPath('data.chef.id', $chef->chefProfile->id)
            ->assertJsonPath('data.chef.userId', $chef->id)
            ->assertJsonPath('data.chef.avatarUrl', 'https://example.test/chef-avatar.jpg')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('chef_diner_bindings', [
            'chef_id' => $chef->id,
            'diner_id' => $diner->id,
            'status' => 'active',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->getJson('/api/diner/chefs')
            ->assertOk()
            ->assertJsonPath('data.0.id', $chef->chefProfile->id)
            ->assertJsonPath('data.0.userId', $chef->id)
            ->assertJsonPath('data.0.displayName', '张师傅');

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->getJson('/api/diner/chefs/'.$chef->id.'/menu')
            ->assertOk()
            ->assertJsonPath('data.chef.id', $chef->chefProfile->id)
            ->assertJsonPath('data.chef.userId', $chef->id)
            ->assertJsonPath('data.dishes.0.id', $activeDish->id)
            ->assertJsonCount(1, 'data.dishes');
    }

    public function test_diner_cannot_view_unbound_chef_menu(): void
    {
        $chef = $this->createChef();
        $diner = User::create([
            'openid' => 'unbound-diner-openid',
            'role' => UserRole::Diner,
            'api_token' => 'unbound-token',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->getJson('/api/diner/chefs/'.$chef->id.'/menu')
            ->assertForbidden();
    }

    public function test_chef_can_list_bound_diners(): void
    {
        $chef = $this->createChef();
        $diner = User::create([
            'openid' => 'bound-diner-openid',
            'role' => UserRole::Diner,
            'nickname' => '小李',
            'api_token' => 'bound-diner-token',
        ]);

        ChefDinerBinding::create([
            'chef_id' => $chef->id,
            'diner_id' => $diner->id,
            'status' => 'active',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->getJson('/api/chef/bindings')
            ->assertOk()
            ->assertJsonPath('data.0.diner.id', $diner->id)
            ->assertJsonPath('data.0.diner.nickname', '小李');
    }

    private function createChef(): User
    {
        $chef = User::create([
            'openid' => 'chef-openid',
            'role' => UserRole::Chef,
            'nickname' => '张师傅',
            'avatar_url' => 'https://example.test/chef-avatar.jpg',
            'api_token' => 'chef-token',
        ]);

        ChefProfile::create([
            'user_id' => $chef->id,
            'display_name' => '张师傅',
            'binding_code' => 'CHEF1234',
        ]);

        return $chef;
    }
}
