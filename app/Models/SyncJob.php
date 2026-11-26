<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** 同步任务 */
class SyncJob extends Model
{
    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'job_type',
        'report_type',
        'status',
        'payload',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function amazonAccount(): BelongsTo
    {
        return $this->belongsTo(AmazonAccount::class);
    }

    public function reportRequests(): HasMany
    {
        return $this->hasMany(ReportRequest::class);
    }

    public function markRunning(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markSucceeded(array $payload = []): void
    {
        $this->update([
            'status' => 'succeeded',
            'finished_at' => now(),
            'payload' => array_merge($this->payload ?? [], $payload),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $message,
        ]);
    }
}
