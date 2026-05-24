<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportJob extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'filename',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'errors',
        'created_by_admin_id',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ImportJobRow, $this>
     */
    public function rows(): HasMany
    {
        return $this->hasMany(ImportJobRow::class);
    }

    /**
     * @return BelongsTo<AdminUser, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by_admin_id');
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function canProcess(): bool
    {
        return $this->status === 'validated' && $this->type === 'product_import';
    }
}
