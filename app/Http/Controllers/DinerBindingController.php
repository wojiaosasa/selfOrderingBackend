<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreDinerBindingRequest;
use App\Models\ChefDinerBinding;
use App\Models\ChefProfile;
use Illuminate\Http\JsonResponse;

class DinerBindingController extends Controller
{
    public function store(StoreDinerBindingRequest $request): JsonResponse
    {
        $diner = $request->user();

        abort_unless($diner->role === UserRole::Diner, 403);

        $profile = ChefProfile::query()
            ->with('user')
            ->where('binding_code', $request->validated('bindingCode'))
            ->firstOrFail();

        $binding = ChefDinerBinding::firstOrCreate(
            [
                'chef_id' => $profile->user_id,
                'diner_id' => $diner->id,
            ],
            ['status' => 'active'],
        );

        return response()->json($this->ok([
            'id' => $binding->id,
            'status' => $binding->status,
            'chef' => [
                'id' => $profile->id,
                'userId' => $profile->user_id,
                'displayName' => $profile->display_name,
                'avatarUrl' => $profile->user->avatar_url,
                'bio' => $profile->bio,
                'serviceNote' => $profile->service_note,
                'isAcceptingOrders' => $profile->is_accepting_orders,
            ],
        ]), 201);
    }
}
