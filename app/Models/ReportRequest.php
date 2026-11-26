<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SP-API 报表请求 */
class ReportRequest extends Model
{
    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'sync_job_id',
        'external_report_id',
        'report_type',
        'processing_status',
        'document_id',
        'request_options',
    ];

    protected function casts(): array
    {
        return [
            'request_options' => 'array',
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

    public function syncJob(): BelongsTo
    {
        return $this->belongsTo(SyncJob::class);
    }
}
