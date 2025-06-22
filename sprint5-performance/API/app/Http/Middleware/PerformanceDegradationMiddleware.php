<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PerformanceDegradationMiddleware
{
    /**
     * Handle an incoming request and simulate performance degradation
     * based on request frequency.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$options)
    {
        // Parse middleware parameters
        $config = $this->parseOptions($options);

        // Generate cache key for tracking requests
        $cacheKey = $this->generateCacheKey($request, $config);

        // Get current request count
        $requestCount = Cache::get($cacheKey, 0);

        // Increment request count
        Cache::put($cacheKey, $requestCount + 1, $config['window']);

        // Calculate and apply performance degradation
        $this->applyPerformanceDegradation($requestCount, $config);

        // Log performance degradation for monitoring
        if ($requestCount >= $config['threshold']) {
            Log::info('Performance degradation applied', [
                'endpoint' => $request->path(),
                'request_count' => $requestCount + 1,
                'delay_applied' => $this->calculateDelay($requestCount, $config),
                'ip' => $request->ip(),
            ]);
        }

        return $next($request);
    }

    /**
     * Parse middleware options with defaults
     */
    private function parseOptions(array $options): array
    {
        $defaults = [
            'threshold' => 10,        // Requests before degradation starts
            'window' => 60,           // Time window in seconds
            'max_delay' => 5000,      // Maximum delay in milliseconds
            'strategy' => 'linear',   // Degradation strategy: linear, exponential, stepped
            'scope' => 'ip',          // Tracking scope: ip, user, global, endpoint
            'degradation_type' => 'blocking', // Type of degradation simulation
        ];

        $config = $defaults;

        // Parse options in format: threshold:10,window:60,max_delay:5000,strategy:exponential,scope:user,degradation_type:memory_intensive
        foreach ($options as $option) {
            $pairs = explode(',', $option);
            foreach ($pairs as $pair) {
                if (strpos($pair, ':') !== false) {
                    [$key, $value] = explode(':', $pair, 2);
                    $config[trim($key)] = is_numeric($value) ? (int)$value : trim($value);
                }
            }
        }

        return $config;
    }

    /**
     * Generate cache key based on scope
     */
    private function generateCacheKey(Request $request, array $config): string
    {
        $baseKey = 'perf_degrade:';

        switch ($config['scope']) {
            case 'ip':
                return $baseKey . 'ip:' . $request->ip() . ':' . $request->path();
            case 'user':
                $userId = $request->user() ? $request->user()->id : $request->ip();
                return $baseKey . 'user:' . $userId . ':' . $request->path();
            case 'global':
                return $baseKey . 'global:' . $request->path();
            case 'endpoint':
                return $baseKey . 'endpoint:' . $request->path();
            default:
                return $baseKey . 'ip:' . $request->ip() . ':' . $request->path();
        }
    }

    /**
     * Apply performance degradation based on strategy
     */
    private function applyPerformanceDegradation(int $requestCount, array $config): void
    {
        if ($requestCount < $config['threshold']) {
            return;
        }

        $delay = $this->calculateDelay($requestCount, $config);

        if ($delay > 0) {
            $this->simulateDelay($delay, $config);
        }
    }

    /**
     * Simulate delay without blocking the entire process
     */
    private function simulateDelay(int $delayMs, array $config): void
    {
        // Always apply the blocking delay
        usleep($delayMs * 1000);

        // If delay == max_delay, introduce a chance to fail after the delay
        if ($delayMs >= $config['max_delay']) {
            $chance = rand(1, 100); // Random chance

            if ($chance <= ($config['timeout_chance'] ?? 20)) { // 20% chance by default
                Log::warning('Simulated timeout after full delay', [
                    'delay_ms' => $delayMs,
                    'ip' => request()->ip(),
                    'endpoint' => request()->path(),
                ]);

                throw new HttpException(504, 'Simulated Gateway Timeout');
            }
        }
    }

    /**
     * Calculate delay based on strategy
     */
    private function calculateDelay(int $requestCount, array $config): int
    {
        $excessRequests = max(0, $requestCount - $config['threshold']);

        switch ($config['strategy']) {
            case 'linear':
                return min(
                    $config['max_delay'],
                    $excessRequests * 100 // 100ms per excess request
                );

            case 'exponential':
                return min(
                    $config['max_delay'],
                    (int)(100 * pow(1.5, $excessRequests)) // Exponential growth
                );

            case 'stepped':
                $steps = [
                    0 => 0,
                    5 => 200,
                    10 => 500,
                    20 => 1000,
                    50 => 2000,
                    100 => $config['max_delay'],
                ];

                foreach (array_reverse($steps, true) as $threshold => $delay) {
                    if ($excessRequests >= $threshold) {
                        return $delay;
                    }
                }
                return 0;

            default:
                return 0;
        }
    }
}
