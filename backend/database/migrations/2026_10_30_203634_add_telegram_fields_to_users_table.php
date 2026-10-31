<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_id', 32)
                ->nullable()
                ->unique()
                ->after('id');

            $table->string('telegram_username')->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->string('language_code', 10)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_admin')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telegram_id']);

            $table->dropColumn([
                'telegram_id',
                'telegram_username',
                'photo_url',
                'language_code',
                'last_login_at',
                'is_admin',
            ]);
        });
    }
};