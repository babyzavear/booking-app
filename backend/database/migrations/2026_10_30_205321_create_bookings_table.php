<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->string('booking_code', 30)->unique();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('business_service_id')
                ->constrained('business_services')
                ->restrictOnDelete();

            $table->foreignId('business_staff_id')
                ->constrained('business_staff')
                ->restrictOnDelete();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            // Harga disimpan sebagai snapshot saat booking.
            $table->decimal('price_snapshot', 12, 2);

            $table->string('status', 30)->default('pending');

            $table->text('customer_notes')->nullable();

            $table->dateTime('cancelled_at')->nullable();

            $table->timestamps();

            $table->index([
                'business_id',
                'start_at',
                'end_at'
            ]);

            $table->index([
                'business_staff_id',
                'start_at',
                'end_at'
            ]);

            $table->index([
                'customer_user_id',
                'status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};