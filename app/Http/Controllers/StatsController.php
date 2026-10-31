<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Predis\Connection\ConnectionException;
use Predis\Response\ServerException;

class StatsController extends Controller
{
    /**
     * Atomically increment stats:hits counter in Redis and return hits.
     */
    public function __invoke(): JsonResponse
    {
        try {
            $hits = Redis::incr('stats:hits');

            return response()->json([
                'hits' => (int) $hits,
            ], 200);
        } catch (ConnectionException|ServerException $e) {
            return response()->json([
                'error' => 'redis_unavailable',
            ], 503);
        }
    }
}
