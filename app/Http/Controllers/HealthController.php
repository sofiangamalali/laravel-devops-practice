<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public const VERSION = '1.0.0';

    /**
     * Return application health status and version.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'version' => self::VERSION,
        ], 200);
    }
}
