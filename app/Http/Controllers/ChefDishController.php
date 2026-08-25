<?php

namespace App\Http\Controllers;

use App\Enums\DishStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreDishRequest;
use App\Http\Requests\UpdateDishRequest;
use App\Http\Requests\UpdateDishStatusRequest;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChefDishController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $chef = $this->chef($request);
        $dishes = $chef->dishes()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Dish $dish): array => $this->serializeDish($dish))
            ->values();

        return response()->json($this->ok($dishes));
    }

    public function store(StoreDishRequest $request): JsonResponse
    {
        $chef = $this->chef($request);
        $dish = Dish::create(array_merge(
            ['chef_id' => $chef->id, 'status' => DishStatus::Active],
            $this->dishAttributes($request->validated()),
        ));

        return response()->json($this->ok($this->serializeDish($dish)), 201);
    }

    public function update(UpdateDishRequest $request, Dish $dish): JsonResponse
    {
        $this->authorizeDish($request, $dish);
        $dish->update($this->dishAttributes($request->validated()));

        return response()->json($this->ok($this->serializeDish($dish->refresh())));
    }

    public function updateStatus(UpdateDishStatusRequest $request, Dish $dish): JsonResponse
    {
        $this->authorizeDish($request, $dish);
        $dish->update(['status' => DishStatus::from($request->validated('status'))]);

        return response()->json($this->ok($this->serializeDish($dish->refresh())));
    }

    public function destroy(Request $request, Dish $dish): JsonResponse
    {
        $this->authorizeDish($request, $dish);
        $dish->delete();

        return response()->json($this->ok(['deleted' => true]));
    }

    private function chef(Request $request): mixed
    {
        $user = $request->user();

        abort_unless($user->role === UserRole::Chef, 403);

        return $user;
    }

    private function authorizeDish(Request $request, Dish $dish): void
    {
        $chef = $this->chef($request);

        abort_unless($dish->chef_id === $chef->id, 403);
    }

    private function dishAttributes(array $validated): array
    {
        $attributes = [];
        $map = [
            'name' => 'name',
            'imageUrl' => 'image_url',
            'category' => 'category',
            'recipe' => 'recipe',
            'tasteNote' => 'taste_note',
            'price' => 'price',
            'portionNote' => 'portion_note',
            'sortOrder' => 'sort_order',
        ];

        foreach ($map as $inputKey => $column) {
            if (array_key_exists($inputKey, $validated)) {
                $attributes[$column] = $validated[$inputKey];
            }
        }

        return $attributes;
    }

    private function serializeDish(Dish $dish): array
    {
        return [
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
        ];
    }
}
