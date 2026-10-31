<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('business_staff_id')
                ->nullable()
                ->constrained('business_staff')
                ->cascadeOnDelete();

            $table->date('exception_date');

            $table->boolean('is_closed')->default(true);

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('reason')->nullable();

            $table->timestamps();

            $table->index([
                'business_id',
                'exception_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};