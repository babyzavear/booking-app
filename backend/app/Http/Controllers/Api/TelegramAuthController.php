<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TelegramAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'initData' => ['required', 'string'],
        ]);

        $initData = $request->input('initData');
        $data = [];

        parse_str($initData, $data);

        $receivedHash = $data['hash'] ?? null;

        if (!$receivedHash) {
            throw ValidationException::withMessages([
                'telegram' => 'Hash Telegram tidak ditemukan.',
            ]);
        }

        unset($data['hash']);

        ksort($data, SORT_STRING);

        $dataCheckString = collect($data)
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->implode("\n");

        $botToken = config('services.telegram.bot_token');

        if (!$botToken) {
            return response()->json([
                'message' => 'Konfigurasi Telegram belum tersedia.',
            ], 500);
        }

        $secretKey = hash_hmac(
            'sha256',
            'WebAppData',
            $botToken,
            true
        );

        $calculatedHash = hash_hmac(
            'sha256',
            $dataCheckString,
            $secretKey
        );

        if (!hash_equals($calculatedHash, $receivedHash)) {
            return response()->json([
                'message' => 'Data autentikasi Telegram tidak valid.',
            ], 401);
        }

        $authDate = (int) ($data['auth_date'] ?? 0);

        // Tolak data yang lebih lama dari 24 jam
        if (!$authDate || abs(time() - $authDate) > 86400) {
            return response()->json([
                'message' => 'Data autentikasi Telegram sudah kedaluwarsa.',
            ], 401);
        }

        $telegramUser = json_decode(
            $data['user'] ?? '',
            true
        );

        if (
            !is_array($telegramUser) ||
            empty($telegramUser['id'])
        ) {
            return response()->json([
                'message' => 'Informasi pengguna Telegram tidak valid.',
            ], 401);
        }

        $user = DB::transaction(function () use ($telegramUser) {
            $name = trim(
                ($telegramUser['first_name'] ?? '') . ' ' .
                ($telegramUser['last_name'] ?? '')
            );

            return User::updateOrCreate(
                [
                    'telegram_id' => (string) $telegramUser['id'],
                ],
                [
                    'name' => $name ?: 'Telegram User',
                    'telegram_username' => $telegramUser['username'] ?? null,
                    'photo_url' => $telegramUser['photo_url'] ?? null,
                    'language_code' => $telegramUser['language_code'] ?? null,
                    'last_login_at' => now(),
                    'password' => Hash::make(Str::random(64)),
                ]
            );
        });

        $token = $user->createToken('bookingmate-miniapp')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'telegram_id' => $user->telegram_id,
                'telegram_username' => $user->telegram_username,
                'photo_url' => $user->photo_url,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}