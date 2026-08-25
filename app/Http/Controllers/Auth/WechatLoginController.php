<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\WechatLoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WechatLoginController extends Controller
{
    public function __invoke(Request $request, WechatLoginService $service): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:128'],
        ]);

        return response()->json($this->ok($service->login($validated['code'])));
    }
}
