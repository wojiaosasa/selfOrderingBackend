<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_business_tables_exist_with_required_columns(): void
    {
        $expectations = [
            'users' => ['id', 'openid', 'role', 'nickname', 'avatar_url', 'phone', 'api_token', 'created_at', 'updated_at'],
            'chef_profiles' => ['id', 'user_id', 'display_name', 'bio', 'service_note', 'is_accepting_orders', 'binding_code', 'created_at', 'updated_at'],
            'dishes' => ['id', 'chef_id', 'name', 'image_url', 'category', 'recipe', 'taste_note', 'price', 'portion_note', 'status', 'sort_order', 'created_at', 'updated_at', 'deleted_at'],
            'chef_diner_bindings' => ['id', 'chef_id', 'diner_id', 'status', 'created_at', 'updated_at'],
            'orders' => ['id', 'order_no', 'chef_id', 'diner_id', 'status', 'expected_time', 'note', 'contact_phone', 'reject_reason', 'created_at', 'updated_at'],
            'order_items' => ['id', 'order_id', 'dish_id', 'dish_snapshot', 'quantity', 'note', 'created_at', 'updated_at'],
        ];

        foreach ($expectations as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}]");
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "Missing column [{$table}.{$column}]");
            }
        }
    }
}
