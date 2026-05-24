<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'note',
        'changed_by_admin_id',
        'changed_by_customer_id',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderStatusHistory $row): void {
            if ($row->created_at === null) {
                $row->created_at = now();
            }
        });
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<AdminUser, $this>
     */
    public function changedByAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'changed_by_admin_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function changedByCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'changed_by_customer_id');
    }
}
