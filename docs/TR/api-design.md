# API Design

This document defines the high-level API endpoints used by the booking system.

---

## Authentication

All endpoints require an authenticated student session.

---

## [GET] /api/class-sessions

Returns available class sessions for the authenticated student.

### Query Parameters

- grade
- subject (optional)
- center (optional)
- date_range (fixed to two weeks)

---

## [POST] /api/bookings

Submits a booking request.

### Request Body

```json
{
  "bookings": [
    {
      "class_session_id": 1,
      "date": "2025-03-18"
    },
    {
      "class_session_id": 1,
      "date": "2025-03-25"
    }
  ],
  "comment": "Optional note",
  "preferences": {
    "preferred_days": ["Mon", "Wed"],
    "preferred_time_slots": ["evening"]
  }
}
```

### Behavior

- Validates active booking constraint

- Executes booking transaction

- Returns final booking statuses

---

## [POST] /api/bookings/{id}/cancel

- Cancels an existing booking.

### Behavior

- Updates booking status to cancelled

- Releases active booking constraint

---

## Booking API Error & Status Mapping

| Scenario                    | HTTP Status              | Error Code                 | Description                                 |
| --------------------------- | ------------------------ | -------------------------- | ------------------------------------------- |
| Duplicate active booking    | 409 Conflict             | ACTIVE_BOOKING_EXISTS      | Student must cancel existing booking first  |
| Invalid date selection      | 422 Unprocessable Entity | INVALID_DATE               | Date outside booking window                 |
| Partial submission attempt  | 422 Unprocessable Entity | INVALID_SELECTION          | Conflicting or invalid class selection      |
| Capacity exceeded           | 200 OK                   | BOOKING_WAITING            | Booking accepted but placed on waiting list |
| Concurrent booking conflict | 200 OK / 409             | BOOKING_WAITING / CONFLICT | Capacity resolved during transaction        |
