<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SP-API 凭证绑定
 * 敏感字段使用 encrypted cast；种子数据仅为占位符，非真实密钥
 */
class AmazonCredential extends Model
{
    protected $fillable = [
        'amazon_account_id',
        'lwa_client_id',
        'lwa_client_secret',
        'lwa_refresh_token',
        'aws_access_key_id',
        'aws_secret_access_key',
        'api_region',
        'role_arn',
        'status',
        'last_validated_at',
    ];

    protected $hidden = [
        'lwa_client_secret',
        'lwa_refresh_token',
        'aws_secret_access_key',
    ];

    protected function casts(): array
    {
        return [
            'lwa_client_id' => 'encrypted',
            'lwa_client_secret' => 'encrypted',
            'lwa_refresh_token' => 'encrypted',
            'aws_access_key_id' => 'encrypted',
            'aws_secret_access_key' => 'encrypted',
            'last_validated_at' => 'datetime',
        ];
    }

    public function amazonAccount(): BelongsTo
    {
        return $this->belongsTo(AmazonAccount::class);
    }
}
