<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingEvent extends Model
{
    protected $fillable = [
        'booking_id',
        'actor_user_id',
        'event_type',
        'old_status',
        'new_status',
        'description',
    ];

    /**
     * Relasi ke booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * User yang melakukan aktivitas.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
