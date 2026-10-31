<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('price', 12, 2);

            $table->unsignedInteger('duration_minutes');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_services');
    }
};