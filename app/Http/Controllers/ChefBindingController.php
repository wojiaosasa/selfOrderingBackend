<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ChefDinerBinding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChefBindingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $chef = $request->user();

        abort_unless($chef->role === UserRole::Chef, 403);

        $bindings = ChefDinerBinding::query()
            ->with('diner')
            ->where('chef_id', $chef->id)
            ->where('status', 'active')
            ->latest('id')
            ->get()
            ->map(fn (ChefDinerBinding $binding): array => [
                'id' => $binding->id,
                'status' => $binding->status,
                'diner' => [
                    'id' => $binding->diner->id,
                    'nickname' => $binding->diner->nickname,
                    'avatarUrl' => $binding->diner->avatar_url,
                    'phone' => $binding->diner->phone,
                ],
            ])
            ->values();

        return response()->json($this->ok($bindings));
    }
}
