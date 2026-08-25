<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Requests\RejectOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChefOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $chef = $this->chef($request);
        $orders = $chef->chefOrders()
            ->with(['items', 'diner'])
            ->latest('id')
            ->get()
            ->map(fn (Order $order): array => OrderSerializer::serialize($order))
            ->values();

        return response()->json($this->ok($orders));
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $chef = $this->chef($request);
        abort_unless($order->chef_id === $chef->id, 403);

        return response()->json($this->ok(OrderSerializer::serialize($order->load(['items', 'chef', 'diner']))));
    }

    public function accept(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return response()->json($this->ok(OrderSerializer::serialize(
            $this->orders->transition($order, OrderStatus::Accepted),
        )));
    }

    public function reject(RejectOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return response()->json($this->ok(OrderSerializer::serialize(
            $this->orders->transition($order, OrderStatus::Rejected, $request->validated('rejectReason')),
        )));
    }

    public function complete(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return response()->json($this->ok(OrderSerializer::serialize(
            $this->orders->transition($order, OrderStatus::Completed),
        )));
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $chef = $this->chef($request);
        abort_unless($order->chef_id === $chef->id, 403);
    }

    private function chef(Request $request): mixed
    {
        $user = $request->user();
        abort_unless($user->role === UserRole::Chef, 403);

        return $user;
    }
}
