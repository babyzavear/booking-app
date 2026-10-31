import { useState } from "react";
import {
  loginWithTelegram,
  type AuthUser,
} from "./services/auth";

import DashboardPage from "./pages/business/DashboardPage";

function App() {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  async function handleLogin() {
    try {
      setLoading(true);
      setError("");

      const result = await loginWithTelegram();

      sessionStorage.setItem(
        "bookingmate_token",
        result.token
      );

      setUser(result.user);
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : "Terjadi kesalahan."
      );
    } finally {
      setLoading(false);
    }
  }

  function handleLogout() {
    sessionStorage.removeItem("bookingmate_token");
    setUser(null);
  }

  if (user) {
    return (
      <DashboardPage
        userName={user.name}
        onLogout={handleLogout}
      />
    );
  }

  return (
    <main className="login-page">
      <div className="login-card">
        <div className="login-logo">
          <span>BM</span>
        </div>

        <h1>BookingMate</h1>

        <p>Book Services. Anytime. Anywhere.</p>

        <button
          className="login-button"
          onClick={handleLogin}
          disabled={loading}
        >
          {loading
            ? "Memproses login..."
            : "Login dengan Telegram"}
        </button>

        {error && <div className="login-error">{error}</div>}
      </div>
    </main>
  );
}

export default App;
