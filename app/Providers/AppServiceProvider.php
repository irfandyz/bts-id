<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinutes(1, 3)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'code' => 429,
                        'status' => 'error',
                        'message' => 'Too many requests. Maximum 3 requests per 60 seconds.',
                        'errors' => null,
                    ], 429);
                });
        });

        RateLimiter::for('products-mutate', function (Request $request) {
            $key = ($request->user()?->id ?? $request->ip());

            return Limit::perSecond(1, 5)
                ->by($key)
                ->response(function () {
                    return response()->json([
                        'code' => 429,
                        'status' => 'error',
                        'message' => 'Too many requests. Please wait 5 seconds before trying again.',
                        'errors' => null,
                    ], 429);
                });
        });
    }
}
