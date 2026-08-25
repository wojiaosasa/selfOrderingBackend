<?php

namespace Tests\Feature;

use App\Enums\DishCategory;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\ChefDinerBinding;
use App\Models\ChefProfile;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_links_to_chef_profile_dishes_bindings_and_orders(): void
    {
        $chef = User::create(['openid' => 'chef-openid', 'role' => UserRole::Chef]);
        $diner = User::create(['openid' => 'diner-openid', 'role' => UserRole::Diner]);

        ChefProfile::create([
            'user_id' => $chef->id,
            'display_name' => '李阿姨',
            'binding_code' => 'ABC123',
        ]);

        $dish = Dish::create([
            'chef_id' => $chef->id,
            'name' => '红烧肉',
            'category' => DishCategory::Meat,
        ]);

        ChefDinerBinding::create([
            'chef_id' => $chef->id,
            'diner_id' => $diner->id,
            'status' => 'active',
        ]);

        $order = Order::create([
            'order_no' => 'SO202608220001',
            'chef_id' => $chef->id,
            'diner_id' => $diner->id,
            'status' => OrderStatus::Pending,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'dish_id' => $dish->id,
            'dish_snapshot' => ['name' => '红烧肉'],
            'quantity' => 2,
        ]);

        $this->assertSame('李阿姨', $chef->chefProfile->display_name);
        $this->assertSame('红烧肉', $chef->dishes()->first()->name);
        $this->assertSame($diner->id, $chef->boundDiners()->first()->id);
        $this->assertSame($chef->id, $diner->boundChefs()->first()->id);
        $this->assertSame(1, $chef->chefOrders()->count());
        $this->assertSame(1, $diner->dinerOrders()->count());
        $this->assertSame('红烧肉', $order->items()->first()->dish_snapshot['name']);
    }
}
