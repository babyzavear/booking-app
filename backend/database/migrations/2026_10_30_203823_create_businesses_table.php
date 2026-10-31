<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'suspended'
            ])->default('pending');

            $table->timestamps();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};