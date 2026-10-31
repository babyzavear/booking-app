<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use App\Models\BookingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessBookingController extends Controller
{
    /**
     * Daftar booking untuk seluruh bisnis milik user login.
     */
    public function index(Request $request)
    {
        $businessIds = Business::where(
            'owner_user_id',
            $request->user()->id
        )->pluck('id');

        $query = Booking::whereIn('business_id', $businessIds);

        if ($request->filled('status')) {
            $request->validate([
                'status' => 'in:pending,confirmed,rejected,cancelled,completed',
            ]);

            $query->where('status', $request->status);
        }

        $bookings = $query
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'message' => 'Daftar booking berhasil diambil.',
            'data' => $bookings,
        ]);
    }

    /**
     * Konfirmasi booking.
     */
    public function confirm(Request $request, $bookingId)
    {
        return $this->changeStatus(
            $request,
            $bookingId,
            'confirmed',
            ['pending'],
            'Booking dikonfirmasi oleh pemilik bisnis.'
        );
    }

    /**
     * Tolak booking.
     */
    public function reject(Request $request, $bookingId)
    {
        return $this->changeStatus(
            $request,
            $bookingId,
            'rejected',
            ['pending'],
            'Booking ditolak oleh pemilik bisnis.'
        );
    }

    /**
     * Tandai booking telah selesai.
     */
    public function complete(Request $request, $bookingId)
    {
        return $this->changeStatus(
            $request,
            $bookingId,
            'completed',
            ['confirmed'],
            'Layanan telah selesai.'
        );
    }

    /**
     * Pemeriksaan kepemilikan dan perubahan status.
     */
    private function changeStatus(
        Request $request,
        $bookingId,
        string $newStatus,
        array $allowedStatuses,
        string $description
    ) {
        $booking = DB::transaction(function () use (
            $request,
            $bookingId,
            $newStatus,
            $allowedStatuses,
            $description
        ) {
            $booking = Booking::where('id', $bookingId)
                ->lockForUpdate()
                ->firstOrFail();

            $isOwner = Business::where('id', $booking->business_id)
                ->where('owner_user_id', $request->user()->id)
                ->exists();

            if (!$isOwner) {
                abort(403, 'Kamu tidak memiliki akses ke booking ini.');
            }

            if (!in_array($booking->status, $allowedStatuses, true)) {
                abort(422, 'Status booking tidak dapat diubah ke tindakan ini.');
            }

            $oldStatus = $booking->status;

            $booking->status = $newStatus;

            $booking->save();

            BookingEvent::create([
                'booking_id' => $booking->id,
                'actor_user_id' => $request->user()->id,
                'event_type' => $newStatus,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => $description,
            ]);

            return $booking->fresh();
        });

        return response()->json([
            'message' => 'Status booking berhasil diperbarui.',
            'data' => $booking,
        ]);
    }
}
