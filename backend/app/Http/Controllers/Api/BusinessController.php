<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $businesses = Business::where(
            'owner_user_id',
            $request->user()->id
        )->with(['category', 'services', 'staff'])
         ->latest()
         ->get();

        return response()->json([
            'data' => $businesses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'province' => ['nullable', 'string'],
        ]);

        $slug = Str::slug($validated['name']);

        $validated['slug'] = $slug . '-' . Str::lower(Str::random(6));
        $validated['owner_user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $business = Business::create($validated);

        return response()->json([
            'message' => 'Bisnis berhasil didaftarkan.',
            'data' => $business,
        ], 201);
    }

    public function show(Request $request, Business $business)
    {
        $this->authorizeOwner($request, $business);

        return response()->json([
            'data' => $business->load([
                'services',
                'staff',
                'hours'
            ])
        ]);
    }

    private function authorizeOwner(
        Request $request,
        Business $business
    ): void {
        abort_unless(
            $business->owner_user_id === $request->user()->id,
            403,
            'Anda tidak memiliki akses ke bisnis ini.'
        );
    }
}