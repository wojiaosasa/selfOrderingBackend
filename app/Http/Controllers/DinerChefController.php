<?php

namespace App\Http\Controllers;

use App\Enums\DishStatus;
use App\Enums\UserRole;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DinerChefController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $diner = $this->diner($request);

        $chefs = $diner->boundChefs()
            ->with('chefProfile')
            ->wherePivot('status', 'active')
            ->get()
            ->map(fn (User $chef): array => $this->serializeChef($chef))
            ->values();

        return response()->json($this->ok($chefs));
    }

    public function menu(Request $request, User $chef): JsonResponse
    {
        $diner = $this->diner($request);

        abort_unless(
            $diner->boundChefs()
                ->where('users.id', $chef->id)
                ->wherePivot('status', 'active')
                ->exists(),
            403,
        );

        $dishes = $chef->dishes()
            ->where('status', DishStatus::Active->value)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Dish $dish): array => [
                'id' => $dish->id,
                'chefId' => $dish->chef_id,
                'name' => $dish->name,
                'imageUrl' => $dish->image_url,
                'category' => $dish->category?->value,
                'recipe' => $dish->recipe,
                'tasteNote' => $dish->taste_note,
                'price' => $dish->price,
                'portionNote' => $dish->portion_note,
                'status' => $dish->status?->value,
                'sortOrder' => $dish->sort_order,
            ])
            ->values();

        return response()->json($this->ok([
            'chef' => $this->serializeChef($chef->loadMissing('chefProfile')),
            'dishes' => $dishes,
        ]));
    }

    private function diner(Request $request): User
    {
        $user = $request->user();

        abort_unless($user->role === UserRole::Diner, 403);

        return $user;
    }

    private function serializeChef(User $chef): array
    {
        $profile = $chef->chefProfile;

        return [
            'id' => $profile?->id,
            'userId' => $chef->id,
            'avatarUrl' => $chef->avatar_url,
            'displayName' => $profile?->display_name ?? $chef->nickname,
            'bio' => $profile?->bio,
            'serviceNote' => $profile?->service_note,
            'isAcceptingOrders' => (bool) $profile?->is_accepting_orders,
        ];
    }
}
