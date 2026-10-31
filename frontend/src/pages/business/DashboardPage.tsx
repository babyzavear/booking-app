import { useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import {
  CalendarDays,
  CheckCircle2,
  Clock3,
  RefreshCw,
  LogOut,
  CalendarCheck,
} from "lucide-react";

import {
  getBusinessBookings,
  updateBookingStatus,
  type BookingStatus,
} from "../../services/bookings";

interface Props {
  userName: string;
  onLogout: () => void;
}

const statusLabels: Record<BookingStatus, string> = {
  pending: "Menunggu",
  confirmed: "Dikonfirmasi",
  rejected: "Ditolak",
  cancelled: "Dibatalkan",
  completed: "Selesai",
};

function formatDate(date: string) {
  return new Date(date).toLocaleString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatPrice(price: string | number) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(Number(price));
}

export default function DashboardPage({
  userName,
  onLogout,
}: Props) {
  const [filter, setFilter] = useState<BookingStatus | "all">("all");
  const queryClient = useQueryClient();

  const bookingsQuery = useQuery({
    queryKey: ["business-bookings", filter],
    queryFn: () =>
      getBusinessBookings(
        filter === "all" ? undefined : filter
      ),
  });

  const mutation = useMutation({
    mutationFn: ({
      id,
      action,
    }: {
      id: number;
      action: "confirm" | "reject" | "complete";
    }) => updateBookingStatus(id, action),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ["business-bookings"],
      });
    },
  });

  const bookings = bookingsQuery.data?.data ?? [];

  const pendingCount = bookings.filter(
    (booking) => booking.status === "pending"
  ).length;

  const confirmedCount = bookings.filter(
    (booking) => booking.status === "confirmed"
  ).length;

  const completedCount = bookings.filter(
    (booking) => booking.status === "completed"
  ).length;

  function handleAction(
    id: number,
    action: "confirm" | "reject" | "complete"
  ) {
    const confirmation = window.confirm(
      action === "confirm"
        ? "Konfirmasi booking ini?"
        : action === "reject"
          ? "Tolak booking ini?"
          : "Tandai booking ini sudah selesai?"
    );

    if (confirmation) {
      mutation.mutate({ id, action });
    }
  }

  return (
    <div className="dashboard">
      <aside className="sidebar">
        <div className="brand">
          <div className="brand-icon">
            <CalendarCheck size={23} />
          </div>
          <div>
            <strong>BookingMate</strong>
            <small>Business Panel</small>
          </div>
        </div>

        <div className="sidebar-menu">
          <p className="menu-label">MENU UTAMA</p>

          <div className="menu-item active">
            <CalendarDays size={19} />
            Dashboard
          </div>

          <div className="menu-item">
            <Clock3 size={19} />
            Bookings
          </div>

          <div className="menu-item">
            <CheckCircle2 size={19} />
            Services
          </div>
        </div>

        <button className="logout-button" onClick={onLogout}>
          <LogOut size={18} />
          Logout
        </button>
      </aside>

      <main className="main-content">
        <header className="topbar">
          <div>
            <h1>Dashboard</h1>
            <p>Selamat datang kembali, {userName}</p>
          </div>

          <div className="user-avatar">
            {userName.charAt(0).toUpperCase()}
          </div>
        </header>

        <section className="welcome-section">
          <div>
            <h2>Kelola booking bisnis kamu</h2>
            <p>
              Pantau pesanan dan kelola jadwal pelanggan
              dari satu tempat.
            </p>
          </div>
          <CalendarCheck size={42} />
        </section>

        <section className="stats-grid">
          <StatCard
            title="Booking ditampilkan"
            value={bookingsQuery.data?.total ?? 0}
            icon={<CalendarDays />}
          />

          <StatCard
            title="Menunggu"
            value={pendingCount}
            icon={<Clock3 />}
          />

          <StatCard
            title="Dikonfirmasi"
            value={confirmedCount}
            icon={<CheckCircle2 />}
          />

          <StatCard
            title="Selesai"
            value={completedCount}
            icon={<CheckCircle2 />}
          />
        </section>

        <section className="booking-section">
          <div className="section-heading">
            <div>
              <h2>Daftar Booking</h2>
              <p>Kelola pesanan pelanggan kamu</p>
            </div>

            <div className="toolbar">
              <select
                value={filter}
                onChange={(event) =>
                  setFilter(event.target.value as BookingStatus | "all")
                }
              >
                <option value="all">Semua status</option>
                <option value="pending">Menunggu</option>
                <option value="confirmed">Dikonfirmasi</option>
                <option value="rejected">Ditolak</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
              </select>

              <button
                className="refresh-button"
                onClick={() => bookingsQuery.refetch()}
                title="Muat ulang"
              >
                <RefreshCw size={17} />
              </button>
            </div>
          </div>

          {bookingsQuery.isLoading && (
            <div className="empty-state">
              Memuat data booking...
            </div>
          )}

          {bookingsQuery.isError && (
            <div className="error-state">
              Gagal mengambil data booking.
              <p>
                Periksa token login dan koneksi Laravel API.
              </p>
              <button onClick={() => bookingsQuery.refetch()}>
                Coba lagi
              </button>
            </div>
          )}

          {!bookingsQuery.isLoading &&
            !bookingsQuery.isError &&
            bookings.length === 0 && (
              <div className="empty-state">
                <CalendarDays size={34} />
                <strong>Belum ada booking</strong>
                <p>Booking pelanggan akan muncul di sini.</p>
              </div>
            )}

          {bookings.length > 0 && (
            <div className="booking-list">
              {bookings.map((booking) => (
                <article className="booking-card" key={booking.id}>
                  <div className="booking-info">
                    <div className="booking-code">
                      {booking.booking_code ||
                        `BOOK-${booking.id}`}
                    </div>

                    <strong>
                      Pelanggan #{booking.customer_user_id}
                    </strong>

                    <p>{formatDate(booking.start_at)}</p>

                    <p>
                      Durasi sampai: {formatDate(booking.end_at)}
                    </p>

                    <strong className="price">
                      {formatPrice(booking.price_snapshot)}
                    </strong>

                    <span className={`status status-${booking.status}`}>
                      {statusLabels[booking.status]}
                    </span>

                    {booking.customer_notes && (
                      <p className="notes">
                        Catatan: {booking.customer_notes}
                      </p>
                    )}
                  </div>

                  <div className="booking-actions">
                    {booking.status === "pending" && (
                      <>
                        <button
                          className="action-confirm"
                          disabled={mutation.isPending}
                          onClick={() =>
                            handleAction(booking.id, "confirm")
                          }
                        >
                          Konfirmasi
                        </button>

                        <button
                          className="action-reject"
                          disabled={mutation.isPending}
                          onClick={() =>
                            handleAction(booking.id, "reject")
                          }
                        >
                          Tolak
                        </button>
                      </>
                    )}

                    {booking.status === "confirmed" && (
                      <button
                        className="action-confirm"
                        disabled={mutation.isPending}
                        onClick={() =>
                          handleAction(booking.id, "complete")
                        }
                      >
                        Selesaikan
                      </button>
                    )}
                  </div>
                </article>
              ))}
            </div>
          )}

          {bookingsQuery.data && (
            <p className="pagination-info">
              Menampilkan halaman {bookingsQuery.data.current_page}
              {" "}dari {bookingsQuery.data.last_page}
            </p>
          )}

          {mutation.isError && (
            <div className="error-state">
              Gagal mengubah status booking.
              Periksa hak akses dan status booking.
            </div>
          )}
        </section>
      </main>
    </div>
  );
}

function StatCard({
  title,
  value,
  icon,
}: {
  title: string;
  value: number;
  icon: React.ReactNode;
}) {
  return (
    <div className="stat-card">
      <div className="stat-icon">{icon}</div>
      <p>{title}</p>
      <h3>{value}</h3>
    </div>
  );
}
