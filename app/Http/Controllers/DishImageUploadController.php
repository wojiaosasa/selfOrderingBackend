<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreDishImageRequest;
use Illuminate\Http\JsonResponse;

class DishImageUploadController extends Controller
{
    public function __invoke(StoreDishImageRequest $request): JsonResponse
    {
        abort_unless($request->user()->role === UserRole::Chef, 403);

        $path = $request->file('image')->store('dish-images', 'public');

        return response()->json($this->ok([
            'url' => '/storage/'.$path,
        ]), 201);
    }
}
