<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'chef_id',
        'diner_id',
        'status',
        'expected_time',
        'note',
        'contact_phone',
        'reject_reason',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'expected_time' => 'datetime',
    ];

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function diner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
