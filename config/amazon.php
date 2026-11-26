<?php

/**
 * Amazon SP-API 配置（全部使用环境变量占位，勿写入真实密钥）
 */
return [
    'stub' => (bool) env('AMAZON_SP_API_STUB', true),

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
