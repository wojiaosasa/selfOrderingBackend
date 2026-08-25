<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\SelectRoleRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\ChefProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->ok($this->serializeUser($request->user())));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'nickname' => $validated['nickname'] ?? $user->nickname,
            'avatar_url' => $validated['avatarUrl'] ?? $user->avatar_url,
            'phone' => array_key_exists('phone', $validated) ? $validated['phone'] : $user->phone,
        ]);

        return response()->json($this->ok($this->serializeUser($user->refresh())));
    }

    public function selectRole(SelectRoleRequest $request): JsonResponse
    {
        $user = $request->user();
        $role = UserRole::from($request->validated('role'));

        $user->update(['role' => $role]);

        if ($role === UserRole::Chef) {
            ChefProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $user->nickname,
                    'binding_code' => $this->newBindingCode(),
                ],
            );
        }

        return response()->json($this->ok($this->serializeUser($user->refresh())));
    }

    private function serializeUser(mixed $user): array
    {
        return [
            'id' => $user->id,
            'openid' => $user->openid,
            'role' => $user->role?->value,
            'nickname' => $user->nickname,
            'avatarUrl' => $user->avatar_url,
            'phone' => $user->phone,
        ];
    }

    private function newBindingCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (ChefProfile::where('binding_code', $code)->exists());

        return $code;
    }
}
