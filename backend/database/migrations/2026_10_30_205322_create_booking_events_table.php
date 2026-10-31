<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('event_type', 50);

            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['booking_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_events');
    }
};