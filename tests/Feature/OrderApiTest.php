<?php

namespace Tests\Feature;

use App\Enums\DishCategory;
use App\Enums\DishStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\ChefDinerBinding;
use App\Models\ChefProfile;
use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_diner_can_create_order_and_chef_can_accept_and_complete_it(): void
    {
        [$chef, $diner, $dish] = $this->boundChefDinerWithDish();

        $orderId = $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->postJson('/api/diner/orders', [
                'chefId' => $chef->id,
                'items' => [
                    ['dishId' => $dish->id, 'quantity' => 2, 'note' => '少盐'],
                ],
                'note' => '晚上六点取',
                'contactPhone' => '13800000000',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.items.0.dishSnapshot.name', '番茄炒蛋')
            ->assertJsonPath('data.items.0.dishSnapshot.imageUrl', 'https://example.test/egg.jpg')
            ->assertJsonPath('data.items.0.dishSnapshot.portionNote', '2人份')
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.contactPhone', '13800000000')
            ->assertJsonPath('data.chef.avatarUrl', 'https://example.test/chef-avatar.jpg')
            ->assertJsonPath('data.diner.avatarUrl', 'https://example.test/diner-avatar.jpg')
            ->assertJsonStructure(['data' => ['createdAt', 'updatedAt']])
            ->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->getJson('/api/chef/orders')
            ->assertOk()
            ->assertJsonPath('data.0.id', $orderId);

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->postJson('/api/chef/orders/'.$orderId.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->postJson('/api/diner/orders/'.$orderId.'/cancel')
            ->assertStatus(422);

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->postJson('/api/chef/orders/'.$orderId.'/complete')
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_diner_can_cancel_pending_order_and_chef_can_reject_pending_order(): void
    {
        [$chef, $diner, $dish] = $this->boundChefDinerWithDish();

        $cancelOrder = Order::create([
            'order_no' => 'SO202608230001',
            'chef_id' => $chef->id,
            'diner_id' => $diner->id,
            'status' => OrderStatus::Pending,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->postJson('/api/diner/orders/'.$cancelOrder->id.'/cancel')
            ->assertOk()
            ->assertJsonPath('data.status', 'canceled');

        $rejectOrderId = $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->postJson('/api/diner/orders', [
                'chefId' => $chef->id,
                'items' => [['dishId' => $dish->id, 'quantity' => 1]],
            ])
            ->assertCreated()
            ->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$chef->api_token)
            ->postJson('/api/chef/orders/'.$rejectOrderId.'/reject', ['rejectReason' => '今天休息'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejectReason', '今天休息');
    }

    public function test_diner_cannot_order_from_unbound_chef(): void
    {
        [$chef, $diner, $dish] = $this->boundChefDinerWithDish();
        ChefDinerBinding::where('chef_id', $chef->id)->where('diner_id', $diner->id)->delete();

        $this->withHeader('Authorization', 'Bearer '.$diner->api_token)
            ->postJson('/api/diner/orders', [
                'chefId' => $chef->id,
                'items' => [['dishId' => $dish->id, 'quantity' => 1]],
            ])
            ->assertForbidden();
    }

    private function boundChefDinerWithDish(): array
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
            'is_accepting_orders' => true,
        ]);

        $diner = User::create([
            'openid' => 'diner-openid',
            'role' => UserRole::Diner,
            'nickname' => '小王',
            'avatar_url' => 'https://example.test/diner-avatar.jpg',
            'api_token' => 'diner-token',
        ]);

        ChefDinerBinding::create([
            'chef_id' => $chef->id,
            'diner_id' => $diner->id,
            'status' => 'active',
        ]);

        $dish = Dish::create([
            'chef_id' => $chef->id,
            'name' => '番茄炒蛋',
            'image_url' => 'https://example.test/egg.jpg',
            'category' => DishCategory::Vegetable,
            'status' => DishStatus::Active,
            'price' => '18',
            'portion_note' => '2人份',
        ]);

        return [$chef, $diner, $dish];
    }
}
