<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Business extends Model
{
    protected $fillable = [
        'owner_user_id',
        'category_id',
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'latitude',
        'longitude',
        'logo',
        'cover_image',
        'status',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(BusinessService::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(BusinessStaff::class);
    }

    public function hours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}