<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UpdateChefProfileRequest;
use App\Models\ChefProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChefProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $this->chefProfile($request);

        return response()->json($this->ok($this->serializeProfile($profile)));
    }

    public function update(UpdateChefProfileRequest $request): JsonResponse
    {
        $profile = $this->chefProfile($request);
        $validated = $request->validated();

        $profile->update([
            'display_name' => $validated['displayName'] ?? $profile->display_name,
            'bio' => array_key_exists('bio', $validated) ? $validated['bio'] : $profile->bio,
            'service_note' => array_key_exists('serviceNote', $validated) ? $validated['serviceNote'] : $profile->service_note,
            'is_accepting_orders' => array_key_exists('isAcceptingOrders', $validated) ? $validated['isAcceptingOrders'] : $profile->is_accepting_orders,
        ]);

        return response()->json($this->ok($this->serializeProfile($profile->refresh())));
    }

    private function chefProfile(Request $request): ChefProfile
    {
        $user = $request->user();

        abort_unless($user->role === UserRole::Chef, 403);

        return $user->chefProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->nickname ?? '',
                'binding_code' => strtoupper(substr(hash('sha256', 'chef-'.$user->id), 0, 8)),
            ],
        );
    }

    private function serializeProfile(ChefProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'userId' => $profile->user_id,
            'displayName' => $profile->display_name,
            'avatarUrl' => $profile->user?->avatar_url ?? '',
            'bio' => $profile->bio,
            'serviceNote' => $profile->service_note,
            'isAcceptingOrders' => $profile->is_accepting_orders,
            'bindingCode' => $profile->binding_code,
        ];
    }
}
