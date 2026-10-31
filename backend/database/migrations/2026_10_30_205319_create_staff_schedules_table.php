<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_staff_id')
                ->constrained('business_staff')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('day_of_week');

            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();

            $table->unique([
                'business_staff_id',
                'day_of_week'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');
    }
};