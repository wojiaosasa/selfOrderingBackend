<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class WechatLoginService
{
    public function login(string $code): array
    {
        $openid = $this->openidFromCode($code);
        $token = hash('sha256', $openid.'|'.Str::random(40));

        $user = User::firstOrCreate(
            ['openid' => $openid],
            [
                'nickname' => '',
                'avatar_url' => '',
            ],
        );

        $user->forceFill(['api_token' => $token])->save();

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'openid' => $user->openid,
                'role' => $user->role?->value,
                'nickname' => $user->nickname,
                'avatarUrl' => $user->avatar_url,
                'phone' => $user->phone,
            ],
        ];
    }

    private function openidFromCode(string $code): string
    {
        $normalized = Str::of($code)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return str_starts_with($normalized, 'dev_') ? $normalized : 'dev_'.$normalized;
    }
}
