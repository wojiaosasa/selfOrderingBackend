<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function ok(mixed $data = null): array
    {
        return [
            'code' => 0,
            'message' => 'ok',
            'data' => $data,
        ];
    }
}
