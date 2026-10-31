const API_URL = "http://127.0.0.1:8000/api";

export interface AuthUser {
  id: number;
  name: string;
  telegram_id: string;
  telegram_username: string | null;
  photo_url: string | null;
}

interface LoginResponse {
  message: string;
  token: string;
  user: AuthUser;
}

export async function loginWithTelegram(): Promise<LoginResponse> {
  const telegram = window.Telegram?.WebApp;

  if (!telegram || !telegram.initData) {
    throw new Error(
      "BookingMate harus dibuka melalui Telegram Mini App."
    );
  }

  telegram.ready();
  telegram.expand();

  const response = await fetch(`${API_URL}/auth/telegram`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({
      initData: telegram.initData,
    }),
  });

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result.message || "Login gagal.");
  }

  return result;
}