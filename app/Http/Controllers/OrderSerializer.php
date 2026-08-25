<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;

class OrderSerializer
{
    public static function serialize(Order $order): array
    {
        $order->loadMissing(['items', 'chef', 'diner']);

        return [
            'id' => $order->id,
            'orderNo' => $order->order_no,
            'chefId' => $order->chef_id,
            'dinerId' => $order->diner_id,
            'status' => $order->status?->value,
            'expectedTime' => $order->expected_time?->toISOString(),
            'note' => $order->note,
            'contactPhone' => $order->contact_phone,
            'rejectReason' => $order->reject_reason,
            'items' => $order->items
                ->map(fn (OrderItem $item): array => [
                    'id' => $item->id,
                    'dishId' => $item->dish_id,
                    'dishSnapshot' => $item->dish_snapshot,
                    'quantity' => $item->quantity,
                    'note' => $item->note,
                ])
                ->values(),
            'chef' => $order->chef ? [
                'id' => $order->chef->id,
                'nickname' => $order->chef->nickname,
                'avatarUrl' => $order->chef->avatar_url,
            ] : null,
            'diner' => $order->diner ? [
                'id' => $order->diner->id,
                'nickname' => $order->diner->nickname,
                'avatarUrl' => $order->diner->avatar_url,
                'phone' => $order->diner->phone,
            ] : null,
            'createdAt' => $order->created_at?->toISOString(),
            'updatedAt' => $order->updated_at?->toISOString(),
        ];
    }
}
