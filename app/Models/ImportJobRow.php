<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportJobRow extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'import_job_id',
        'row_number',
        'raw_data',
        'status',
        'errors',
        'created_record_type',
        'created_record_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'errors' => 'array',
        ];
    }

    /**
     * @return BelongsTo<ImportJob, $this>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(ImportJob::class, 'import_job_id');
    }
}
