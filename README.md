# Class Booking System

A refactored class booking system migrating from a legacy PHP/jQuery implementation to Laravel, with a focus on backend-driven design and concurrency-safe booking workflows.

---

## Project Background

The original system was implemented as a single-page booking interface using plain PHP and jQuery.  
While functional, the architecture relied heavily on client-side state management and lacked clear separation between business rules and persistence logic.

This project revisits the same booking problem with the goal of designing a more maintainable and backend-oriented solution.

---

## Design Goals

- Manage booking-related business logic and validation consistently with a server-centric approach.
- Use client-side validation only as a supplementary aid for user experience.
- Design a booking flow that guarantees data consistency even under concurrent requests.
- Prioritize clear design documentation and decision rationale over implementation details.

---

## Key Design Decisions

### Hybrid UI Approach

The user interface behaves like a single-page application, but all critical validation and state transitions are handled on the server.

### No Temporary Persistence

Incomplete booking data is not stored in the database.  
Only final submissions result in persisted booking records.

### Concurrency Handling at Submission Time

Class capacity is validated again at submission time to safely handle concurrent booking attempts.

### Payment Excluded

No payment is processed during booking.  
Payments are handled separately after administrative confirmation.

### Single Active Booking Policy

Each student may have only one active booking batch at a time.  
To submit a new booking request, existing bookings must be cancelled first.  
This decision simplifies state management and ensures booking consistency.

---

## System Overview

- Students can browse and filter class sessions by subject and center
- Bookings are allowed within a two-week window
- Multiple centers can be selected with travel time considerations
- Full classes result in waiting list bookings
- Students must cancel existing bookings before submitting a new booking request

---

## Documentation

- [Use Cases](docs/UseCases.md)
- [Functional Requirements](docs/FR.md)
- [Technical Requirements](docs/TR.md)
- [ERD Diagram](docs/diagrams/erd.md)

---

## Technology Stack

- Backend: Laravel
- Frontend: Blade + JavaScript
- Database: MySQL
- Testing: PHPUnit (planned)
- API design: Documentation of RESTful endpoint design (TR.md)

---

## Notes

In the original system, booking data was also exported to Google Docs and administrators were notified via Slack.  
These integrations are documented but not fully implemented in this refactored version to keep the focus on core system design.

---

## Status

This project is under active development and focuses on design-first implementation.
