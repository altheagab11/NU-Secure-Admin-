<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    public const TYPE_DAILY_VISITOR = 'daily_visitor';

    public const TYPE_DATE_RANGE = 'date_range';

    public const STATUS_PENDING = 'pending';

    public const STATUS_GENERATING = 'generating';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'daily_reports';

    protected $fillable = [
        'report_date',
        'date_range_end',
        'report_type',
        'file_name',
        'file_path',
        'record_count',
        'generation_status',
        'generated_at',
        'generated_by',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'date_range_end' => 'date',
            'generated_at' => 'datetime',
            'record_count' => 'integer',
            'generated_by' => 'integer',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }

    public function isDownloadable(): bool
    {
        return $this->generation_status === self::STATUS_COMPLETED
            && filled($this->file_path)
            && filled($this->file_name);
    }

    public function isDateRangeReport(): bool
    {
        return $this->report_type === self::TYPE_DATE_RANGE;
    }
}
