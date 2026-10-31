<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\BusinessStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
            'business_service_id' => [
                'required',
                'exists:business_services,id'
            ],
            'business_staff_id' => [
                'required',
                'exists:business_staff,id'
            ],
            'start_at' => ['required', 'date', 'after:now'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use (
            $validated,
            $request
        ) {
            // Lock bisnis untuk mencegah proses booking bersamaan
            // melewati pemeriksaan jadwal.
            $business = Business::where(
                'id',
                $validated['business_id']
            )->lockForUpdate()->firstOrFail();

            if ($business->status !== 'active') {
                throw ValidationException::withMessages([
                    'business' => 'Bisnis belum aktif.'
                ]);
            }

            $service = BusinessService::where(
                'id',
                $validated['business_service_id']
            )->where('business_id', $business->id)
             ->where('is_active', true)
             ->firstOrFail();

            $staff = BusinessStaff::where(
                'id',
                $validated['business_staff_id']
            )->where('business_id', $business->id)
             ->where('is_active', true)
             ->firstOrFail();

            $startAt = Carbon::parse($validated['start_at']);

            // Durasi berasal dari data layanan, bukan frontend.
            $endAt = $startAt->copy()->addMinutes(
                $service->duration_minutes
            );

            // Cek apakah jadwal staf sudah terisi.
            $conflict = Booking::where(
                'business_staff_id',
                $staff->id
            )
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'schedule' => 'Jadwal tersebut sudah dipesan.'
                ]);
            }

            $booking = Booking::create([
                'booking_code' => 'BM-' .
                    now()->format('ymd') . '-' .
                    Str::upper(Str::random(6)),

                'business_id' => $business->id,

                'customer_user_id' => $request->user()->id,

                'business_service_id' => $service->id,

                'business_staff_id' => $staff->id,

                'start_at' => $startAt,

                'end_at' => $endAt,

                'price_snapshot' => $service->price,

                'status' => 'pending',

                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            $booking->events()->create([
                'actor_user_id' => $request->user()->id,
                'event_type' => 'booking_created',
                'old_status' => null,
                'new_status' => 'pending',
                'description' => 'Booking dibuat oleh pelanggan.',
            ]);

            return $booking;
        }, 3);

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'data' => $booking->load([
                'business',
                'service',
                'staff'
            ]),
        ], 201);
    }

    public function myBookings(Request $request)
    {
        $bookings = Booking::where(
            'customer_user_id',
            $request->user()->id
        )
        ->with(['business', 'service', 'staff'])
        ->latest()
        ->paginate(15);

        return response()->json($bookings);
    }

    public function cancel(Request $request, Booking $booking)
    {
        abort_unless(
            $booking->customer_user_id === $request->user()->id,
            403,
            'Anda tidak memiliki akses ke booking ini.'
        );

        DB::transaction(function () use ($booking, $request) {
            $lockedBooking = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array(
                $lockedBooking->status,
                ['pending', 'confirmed'],
                true
            )) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking tidak dapat dibatalkan.'
                ]);
            }

            $oldStatus = $lockedBooking->status;

            $lockedBooking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $lockedBooking->events()->create([
                'actor_user_id' => $request->user()->id,
                'event_type' => 'booking_cancelled',
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'description' => 'Booking dibatalkan pelanggan.',
            ]);
        });

        return response()->json([
            'message' => 'Booking berhasil dibatalkan.'
        ]);
    }
}