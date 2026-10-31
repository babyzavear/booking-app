<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_staff', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('position')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_staff');
    }
};