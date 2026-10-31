<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'business_id',
        'customer_user_id',
        'business_service_id',
        'business_staff_id',
        'start_at',
        'end_at',
        'price_snapshot',
        'status',
        'customer_notes',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'price_snapshot' => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            BusinessService::class,
            'business_service_id'
        );
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(
            BusinessStaff::class,
            'business_staff_id'
        );
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingEvent::class);
    }
}