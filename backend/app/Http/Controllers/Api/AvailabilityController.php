<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\BusinessHour;
use App\Models\BusinessStaff;
use App\Models\StaffSchedule;
use App\Models\ScheduleException;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        // Validasi parameter
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:business_services,id',
            'date' => 'required|date_format:Y-m-d',
            'staff_id' => 'nullable|integer',
        ]);

        $timezone = 'Asia/Jakarta';
        $date = Carbon::parse($validated['date'], $timezone);

        if ($date->lt(Carbon::today($timezone))) {
            return response()->json([
                'message' => 'Tanggal booking tidak boleh di masa lalu.',
                'data' => [],
            ], 422);
        }

        $business = Business::findOrFail($validated['business_id']);

        if ($business->status !== 'active') {
            return response()->json([
                'message' => 'Bisnis tidak tersedia.',
            ], 422);
        }

        $service = BusinessService::where('business_id', $business->id)
            ->where('id', $validated['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $day = $date->dayOfWeek;

        $businessHour = BusinessHour::where('business_id', $business->id)
            ->where('day_of_week', $day)
            ->first();

        if (!$businessHour || $businessHour->is_closed) {
            return response()->json([
                'message' => 'Bisnis tutup pada tanggal tersebut.',
                'data' => [],
            ]);
        }

        $businessException = ScheduleException::where(
            'business_id',
            $business->id
        )
            ->whereNull('business_staff_id')
            ->whereDate('exception_date', $validated['date'])
            ->first();

        if ($businessException && $businessException->is_closed) {
            return response()->json([
                'message' => 'Bisnis tutup pada tanggal tersebut.',
                'data' => [],
            ]);
        }

        $openTime = $businessException?->start_time
            ?? $businessHour->open_time;

        $closeTime = $businessException?->end_time
            ?? $businessHour->close_time;

        $businessOpen = Carbon::parse(
            $validated['date'] . ' ' . $openTime,
            $timezone
        );

        $businessClose = Carbon::parse(
            $validated['date'] . ' ' . $closeTime,
            $timezone
        );

        $staffQuery = BusinessStaff::where('business_id', $business->id)
            ->where('is_active', true);

        if (!empty($validated['staff_id'])) {
            $staffQuery->where('id', $validated['staff_id']);
        }

        $staffMembers = $staffQuery->get();

        $duration = (int) $service->duration_minutes;
        $result = [];

        foreach ($staffMembers as $staff) {
            $schedule = StaffSchedule::where(
                'business_staff_id',
                $staff->id
            )
                ->where('day_of_week', $day)
                ->first();

            if (!$schedule) {
                continue;
            }

            $staffException = ScheduleException::where(
                'business_id',
                $business->id
            )
                ->where('business_staff_id', $staff->id)
                ->whereDate('exception_date', $validated['date'])
                ->first();

            if ($staffException && $staffException->is_closed) {
                continue;
            }

            $staffStart = $staffException?->start_time
                ?? $schedule->start_time;

            $staffEnd = $staffException?->end_time
                ?? $schedule->end_time;

            $shiftStart = Carbon::parse(
                $validated['date'] . ' ' . $staffStart,
                $timezone
            );

            $shiftEnd = Carbon::parse(
                $validated['date'] . ' ' . $staffEnd,
                $timezone
            );

            $start = $businessOpen->copy()->max($shiftStart);
            $end = $businessClose->copy()->min($shiftEnd);

            $slots = [];

            for (
                $slot = $start->copy();
                $slot->copy()->addMinutes($duration)->lte($end);
                $slot->addMinutes(15)
            ) {
                $slotEnd = $slot->copy()->addMinutes($duration);

                $hasConflict = Booking::where(
                    'business_staff_id',
                    $staff->id
                )
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where('start_at', '<', $slotEnd)
                    ->where('end_at', '>', $slot)
                    ->exists();

                if (!$hasConflict) {
                    $slots[] = [
                        'start_at' => $slot->format('Y-m-d H:i:s'),
                        'end_at' => $slotEnd->format('Y-m-d H:i:s'),
                        'time' => $slot->format('H:i'),
                    ];
                }
            }

            $result[] = [
                'staff_id' => $staff->id,
                'staff_name' => $staff->name,
                'slots' => $slots,
            ];
        }

        return response()->json([
            'business' => $business->name,
            'service' => $service->name,
            'date' => $validated['date'],
            'duration_minutes' => $duration,
            'data' => $result,
        ]);
    }
}
