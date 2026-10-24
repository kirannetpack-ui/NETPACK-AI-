<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class SystemHealthController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    public function readiness(): JsonResponse
    {
        $checks = [
            'database' => $this->databaseIsReady(),
            'cache' => $this->cacheIsReady(),
        ];
        $ready = !in_array(false, $checks, true);

        return response()->json([
            'status' => $ready ? 'ready' : 'degraded',
            'checks' => $checks,
        ], $ready ? 200 : 503);
    }

    private function databaseIsReady(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function cacheIsReady(): bool
    {
        $key = 'health:readiness:'.bin2hex(random_bytes(8));

        try {
            Cache::put($key, 'ok', 10);
            $ready = Cache::get($key) === 'ok';
            Cache::forget($key);

            return $ready;
        } catch (Throwable) {
            return false;
        }
    }
}
