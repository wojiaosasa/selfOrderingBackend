<?php

namespace App\Services;

use App\Enums\DishStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\ChefDinerBinding;
use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createForDiner(User $diner, array $payload): Order
    {
        abort_unless($diner->role === UserRole::Diner, 403);

        $chef = User::with('chefProfile')->findOrFail($payload['chefId']);
        abort_unless($chef->role === UserRole::Chef, 403);
        abort_unless($chef->chefProfile?->is_accepting_orders, 422);
        abort_unless($this->isBound($chef->id, $diner->id), 403);

        return DB::transaction(function () use ($chef, $diner, $payload): Order {
            $order = Order::create([
                'order_no' => $this->newOrderNo(),
                'chef_id' => $chef->id,
                'diner_id' => $diner->id,
                'status' => OrderStatus::Pending,
                'expected_time' => $payload['expectedTime'] ?? null,
                'note' => $payload['note'] ?? null,
                'contact_phone' => $payload['contactPhone'] ?? null,
            ]);

            foreach ($payload['items'] as $item) {
                $dish = Dish::query()
                    ->where('chef_id', $chef->id)
                    ->where('status', DishStatus::Active->value)
                    ->findOrFail($item['dishId']);

                $order->items()->create([
                    'dish_id' => $dish->id,
                    'dish_snapshot' => [
                        'id' => $dish->id,
                        'name' => $dish->name,
                        'imageUrl' => $dish->image_url,
                        'category' => $dish->category?->value,
                        'price' => $dish->price,
                        'portionNote' => $dish->portion_note,
                    ],
                    'quantity' => $item['quantity'],
                    'note' => $item['note'] ?? null,
                ]);
            }

            return $order->load(['items', 'diner', 'chef']);
        });
    }

    public function transition(Order $order, OrderStatus $to, ?string $rejectReason = null): Order
    {
        $from = $order->status;

        $allowed = match ($to) {
            OrderStatus::Accepted => $from === OrderStatus::Pending,
            OrderStatus::Rejected => $from === OrderStatus::Pending,
            OrderStatus::Canceled => $from === OrderStatus::Pending,
            OrderStatus::Completed => $from === OrderStatus::Accepted,
            default => false,
        };

        abort_unless($allowed, 422);

        $order->update([
            'status' => $to,
            'reject_reason' => $to === OrderStatus::Rejected ? $rejectReason : $order->reject_reason,
        ]);

        return $order->refresh()->load(['items', 'diner', 'chef']);
    }

    private function isBound(int $chefId, int $dinerId): bool
    {
        return ChefDinerBinding::query()
            ->where('chef_id', $chefId)
            ->where('diner_id', $dinerId)
            ->where('status', 'active')
            ->exists();
    }

    private function newOrderNo(): string
    {
        do {
            $orderNo = 'SO'.now()->format('YmdHis').random_int(1000, 9999);
        } while (Order::where('order_no', $orderNo)->exists());

        return $orderNo;
    }
}
