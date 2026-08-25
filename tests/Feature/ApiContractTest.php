<?php

namespace Tests\Feature;

use App\Enums\DishCategory;
use App\Enums\DishStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    public function test_business_enums_match_mvp_contract(): void
    {
        $this->assertSame(['chef', 'diner'], array_column(UserRole::cases(), 'value'));
        $this->assertSame(['meat', 'small_meat', 'vegetable', 'soup'], array_column(DishCategory::cases(), 'value'));
        $this->assertSame(['active', 'inactive'], array_column(DishStatus::cases(), 'value'));
        $this->assertSame(['pending', 'accepted', 'rejected', 'completed', 'canceled'], array_column(OrderStatus::cases(), 'value'));
    }

    public function test_mvp_api_routes_are_registered(): void
    {
        $expectedRoutes = [
            'api.wechat.login',
            'api.me.show',
            'api.me.profile.update',
            'api.me.role.store',
            'api.chef.profile.show',
            'api.chef.profile.update',
            'api.chef.dishes.index',
            'api.chef.dishes.store',
            'api.chef.dishes.update',
            'api.chef.dishes.status',
            'api.chef.dishes.destroy',
            'api.chef.bindings.index',
            'api.chef.orders.index',
            'api.chef.orders.show',
            'api.chef.orders.accept',
            'api.chef.orders.reject',
            'api.chef.orders.complete',
            'api.diner.bindings.store',
            'api.diner.chefs.index',
            'api.diner.chefs.menu',
            'api.diner.orders.store',
            'api.diner.orders.index',
            'api.diner.orders.show',
            'api.diner.orders.cancel',
            'api.uploads.dish-image.store',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertNotNull(Route::getRoutes()->getByName($routeName), "Missing route [{$routeName}]");
        }
    }
}
