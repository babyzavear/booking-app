import api from "./axios";

export type BookingStatus =
  | "pending"
  | "confirmed"
  | "rejected"
  | "cancelled"
  | "completed";

export interface Booking {
  id: number;
  booking_code?: string;
  business_id: number;
  customer_user_id: number;
  business_service_id: number;
  business_staff_id: number;
  start_at: string;
  end_at: string;
  price_snapshot: string | number;
  status: BookingStatus;
  customer_notes?: string | null;
  created_at: string;
}

interface BookingListResponse {
  message: string;
  data: {
    data: Booking[];
    current_page: number;
    last_page: number;
    total: number;
  };
}

export async function getBusinessBookings(
  status?: BookingStatus
) {
  const response = await api.get<BookingListResponse>(
    "/business/bookings",
    {
      params: status ? { status } : {},
    }
  );

  return response.data.data;
}

export async function updateBookingStatus(
  bookingId: number,
  action: "confirm" | "reject" | "complete"
) {
  const response = await api.post(
    `/business/bookings/${bookingId}/${action}`
  );

  return response.data;
}
