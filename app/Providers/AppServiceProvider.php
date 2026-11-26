<?php

namespace App\Providers;

use App\Contracts\AmazonSpApiClient;
use App\Services\Amazon\FixtureAmazonSpApiClient;
use App\Services\Amazon\StubAmazonSpApiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AmazonSpApiClient::class, function ($app) {
            $driver = strtolower((string) config('amazon.driver', 'fixture'));

            return match ($driver) {
                'stub' => $app->make(StubAmazonSpApiClient::class),
                'http', 'live', 'sp-api' => $this->resolveHttpDriver($app),
                default => $app->make(FixtureAmazonSpApiClient::class),
            };
        });
    }

    public function boot(): void
    {
        //
    }

    /** 真实 HTTP 客户端尚未实现：安全回退到 fixture，避免误调外网 */
    protected function resolveHttpDriver($app): AmazonSpApiClient
    {
        Log::warning('AMAZON_DRIVER=http 尚未实现，已回退到 fixture 驱动（无真实 Amazon 调用）。');

        return $app->make(FixtureAmazonSpApiClient::class);
    }
}
