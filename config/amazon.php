<?php

/**
 * Amazon SP-API 配置
 *
 * 驱动（AMAZON_DRIVER）：
 * - fixture（默认）：JSON fixtures，无真实网络
 * - stub：进程内简易假数据
 * - http：预留真实客户端（未实现；选择后会回退 fixture 并打日志）
 *
 * 凭证环境变量仅为占位，切勿提交真实密钥。
 */
return [
    'driver' => env('AMAZON_DRIVER', 'fixture'),

    // 兼容旧变量：AMAZON_SP_API_STUB=true 且未设置 DRIVER 时仍视为模拟
    'stub' => (bool) env('AMAZON_SP_API_STUB', true),

    'fixtures' => [
        // 空字符串视为未设置，回退到默认 resources/amazon/fixtures
        'path' => (($p = env('AMAZON_FIXTURES_PATH')) && $p !== '')
            ? $p
            : resource_path('amazon/fixtures'),
    ],

    'lwa' => [
        'client_id' => env('AMAZON_SP_API_CLIENT_ID'),
        'client_secret' => env('AMAZON_SP_API_CLIENT_SECRET'),
        'refresh_token' => env('AMAZON_SP_API_REFRESH_TOKEN'),
        'endpoint' => env('AMAZON_LWA_ENDPOINT', 'https://api.amazon.com/auth/o2/token'),
    ],

    'aws' => [
        'access_key_id' => env('AMAZON_SP_API_ACCESS_KEY_ID'),
        'secret_access_key' => env('AMAZON_SP_API_SECRET_ACCESS_KEY'),
    ],

    'api' => [
        'region' => env('AMAZON_SP_API_REGION', 'na'),
        'endpoint' => env('AMAZON_SP_API_ENDPOINT', 'https://sellingpartnerapi-na.amazon.com'),
    ],

    'regions' => [
        'na' => 'https://sellingpartnerapi-na.amazon.com',
        'eu' => 'https://sellingpartnerapi-eu.amazon.com',
        'fe' => 'https://sellingpartnerapi-fe.amazon.com',
    ],
];
