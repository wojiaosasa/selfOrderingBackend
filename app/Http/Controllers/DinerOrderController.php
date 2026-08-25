<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DinerOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $diner = $this->diner($request);
        $orders = $diner->dinerOrders()
            ->with(['items', 'chef'])
            ->latest('id')
            ->get()
            ->map(fn (Order $order): array => $this->serializeOrder($order))
            ->values();

        return response()->json($this->ok($orders));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orders->createForDiner($this->diner($request), $request->validated());

        return response()->json($this->ok($this->serializeOrder($order)), 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $diner = $this->diner($request);
        abort_unless($order->diner_id === $diner->id, 403);

        return response()->json($this->ok($this->serializeOrder($order->load(['items', 'chef', 'diner']))));
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $diner = $this->diner($request);
        abort_unless($order->diner_id === $diner->id, 403);

        return response()->json($this->ok($this->serializeOrder(
            $this->orders->transition($order, OrderStatus::Canceled),
        )));
    }

    private function diner(Request $request): mixed
    {
        $user = $request->user();
        abort_unless($user->role === UserRole::Diner, 403);

        return $user;
    }

    private function serializeOrder(Order $order): array
    {
        return OrderSerializer::serialize($order);
    }
}
