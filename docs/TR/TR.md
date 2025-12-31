# Technical Requirements (TR)

This document defines the technical constraints, system-level behaviors, and architectural decisions for the class booking system.  
It focuses on _how the system must operate_ rather than _what users can do_.

Implementation details such as controller structure or class naming may evolve during development.

---

## TR-01 Authentication & Authorization

The system identifies students using Laravel authentication.

- Only authenticated students may access booking-related endpoints.
- All booking operations are authorized based on the authenticated student’s identity.
- Booking data is always associated with the requesting student.

---

## TR-02 Booking Access Constraints

The system enforces the following access constraints at the server level:

- Each student may have only one **active booking batch** at a time.
- Students with existing active bookings must cancel them before submitting a new booking request.
- Attempts to bypass this constraint are rejected by the server.

---

## TR-03 Booking Submission Model

The booking flow may appear as a multi-step UI on the client, but:

- The system exposes a **single submission endpoint** for booking creation.
- All validation, availability checks, and state transitions occur at submission time.
- Intermediate steps do not trigger database write operations.

---

## TR-04 Persistence Boundaries

The system clearly defines persistence boundaries to avoid partial or inconsistent data:

- Incomplete or intermediate booking selections are **not persisted**.
- Booking records are created only after successful final submission.
- No temporary booking or draft records exist in the database.

---

## TR-05 Concurrency Handling

The system guarantees data consistency under concurrent booking requests.

- Class capacity is validated at submission time.
- Capacity checks and booking creation occur within a single database transaction.
- Row-level locking is applied to the relevant class session during booking creation.
- The system prevents capacity overruns even under simultaneous requests.

---

## TR-06 Booking Status Determination

Booking status is determined atomically during submission:

- If capacity is available, the booking is created with status `confirmed`.
- If capacity is exceeded, the booking is created with status `waiting`.
- Status assignment is final for that submission and communicated to the student.

---

## TR-07 Cancellation Handling

The system supports explicit cancellation of bookings:

- Students may cancel their own bookings.
- Cancellation updates booking status to `cancelled`.
- Cancelling a booking releases the student’s active booking constraint, allowing new submissions.

---

## TR-08 Notification Integration (Documented)

The system documents, but does not fully implement, external notification integrations:

- Booking submissions trigger notifications to administrators.
- Confirmation emails are sent to students.
- Legacy integrations (e.g., Google Docs export, Slack notifications) are recorded for reference.

---

## TR-09 Infrastructure Assumptions

The system operates under the following infrastructure assumptions:

- Backend framework: Laravel
- Database: MySQL (relational, transactional)
- Server-side rendering with Blade templates
- Client-side JavaScript used for UX enhancement only

---

## Scope Note

This document defines **technical constraints and guarantees**, not implementation details.

- Controller, service, and repository structures may evolve.
- Detailed API contracts, database schemas, and transaction pseudocode are documented separately.

---

## Related Documents

- API Design: `api-design.md`
- Database Schema: `db-schema.md`
- Booking Transaction Design: `booking-transaction.md`
