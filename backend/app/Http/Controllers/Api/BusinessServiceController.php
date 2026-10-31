<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessService;
use Illuminate\Http\Request;

class BusinessServiceController extends Controller
{
    public function index(Business $business)
    {
        return response()->json([
            'data' => $business->services()
                ->latest()
                ->get()
        ]);
    }

    public function store(Request $request, Business $business)
    {
        abort_unless(
            $business->owner_user_id === $request->user()->id,
            403
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
        ]);

        $service = $business->services()->create($validated);

        return response()->json([
            'message' => 'Layanan berhasil ditambahkan.',
            'data' => $service,
        ], 201);
    }

    public function update(
        Request $request,
        Business $business,
        BusinessService $service
    ) {
        abort_unless(
            $business->owner_user_id === $request->user()->id &&
            $service->business_id === $business->id,
            403
        );

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'duration_minutes' => [
                'sometimes',
                'required',
                'integer',
                'min:5',
                'max:1440'
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $service->update($validated);

        return response()->json([
            'message' => 'Layanan berhasil diperbarui.',
            'data' => $service,
        ]);
    }

    public function destroy(
        Request $request,
        Business $business,
        BusinessService $service
    ) {
        abort_unless(
            $business->owner_user_id === $request->user()->id &&
            $service->business_id === $business->id,
            403
        );

        // Nonaktifkan layanan, jangan hapus riwayat booking.
        $service->update(['is_active' => false]);

        return response()->json([
            'message' => 'Layanan berhasil dinonaktifkan.'
        ]);
    }
}