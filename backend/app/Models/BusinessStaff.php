<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessStaff extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'name',
        'phone',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}