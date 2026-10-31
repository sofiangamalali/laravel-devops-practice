<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class CrashController extends Controller
{
    /**
     * Terminate the php-fpm master process with SIGKILL if ENABLE_CRASH is '1', else return 404 JSON.
     */
    public function __invoke(): JsonResponse
    {
        if ((string) config('app_extra.enable_crash') === '1') {
            $ppid = posix_getppid();
            posix_kill($ppid, 9);

            return response()->json([
                'error' => 'crash_failed',
            ], 500);
        }

        return response()->json([
            'error' => 'not_found',
        ], 404);
    }
}
