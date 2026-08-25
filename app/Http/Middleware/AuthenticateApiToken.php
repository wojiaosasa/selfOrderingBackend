<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (! $user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
