<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TelegramAuthController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\BusinessServiceController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BusinessBookingController;

/*
|--------------------------------------------------------------------------
| Public Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/telegram', [
        TelegramAuthController::class,
        'login'
    ])->middleware('throttle:10,1');
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::get('/auth/me', [
        TelegramAuthController::class,
        'me'
    ]);

    Route::post('/auth/logout', [
        TelegramAuthController::class,
        'logout'
    ]);

    // Business Management
    Route::get('/businesses', [
        BusinessController::class,
        'index'
    ]);

    Route::post('/businesses', [
        BusinessController::class,
        'store'
    ]);

    Route::get('/businesses/{business}', [
        BusinessController::class,
        'show'
    ]);

    // Business Services
    Route::get('/businesses/{business}/services', [
        BusinessServiceController::class,
        'index'
    ]);

    Route::post('/businesses/{business}/services', [
        BusinessServiceController::class,
        'store'
    ]);

    Route::put('/businesses/{business}/services/{service}', [
        BusinessServiceController::class,
        'update'
    ]);

    Route::delete('/businesses/{business}/services/{service}', [
        BusinessServiceController::class,
        'destroy'
    ]);

    // Booking
    Route::post('/bookings', [
        BookingController::class,
        'store'
    ]);

    Route::get('/my-bookings', [
        BookingController::class,
        'myBookings'
    ]);

    Route::post('/bookings/{booking}/cancel', [
        BookingController::class,
        'cancel'
    ]);

    // Availability
    Route::get('/availability', [
        AvailabilityController::class,
        'index'
    ]);

    // Business Booking Management
    Route::get(
    '/business/bookings',
    [BusinessBookingController::class, 'index']
    );

    Route::post(
        '/business/bookings/{booking}/confirm',
        [BusinessBookingController::class, 'confirm']
    );

    Route::post(
        '/business/bookings/{booking}/reject',
        [BusinessBookingController::class, 'reject']
    );

    Route::post(
        '/business/bookings/{booking}/complete',
        [BusinessBookingController::class, 'complete']
    );

});