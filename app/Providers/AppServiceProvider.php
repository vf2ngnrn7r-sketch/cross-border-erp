<?php

namespace App\Providers;

use App\Contracts\AmazonSpApiClient;
use App\Services\Amazon\StubAmazonSpApiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 默认绑定桩客户端；后续可按 config('amazon.stub') 切换真实实现
        $this->app->bind(AmazonSpApiClient::class, StubAmazonSpApiClient::class);
    }

    public function boot(): void
    {
        //
    }
}
