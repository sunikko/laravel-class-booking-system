# Class Booking System

A refactored class booking system migrating from a legacy PHP/jQuery implementation to Laravel, with a focus on backend-driven design and concurrency-safe booking workflows.

---

## Project Background

The original system was implemented as a single-page booking interface using plain PHP and jQuery.  
While functional, the architecture relied heavily on client-side state management and lacked clear separation between business rules and persistence logic.

This project revisits the same booking problem with the goal of designing a more maintainable and backend-oriented solution.

---

## Design Goals

-   Manage booking-related business logic and validation consistently with a server-centric approach.
-   Use client-side validation only as a supplementary aid for user experience.
-   Design a booking flow that guarantees data consistency even under concurrent requests.
-   Prioritize clear design documentation and decision rationale over implementation details.

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

-   Students can browse and filter class sessions by subject and center
-   Bookings are allowed within a two-week window
-   Multiple centers can be selected with travel time considerations
-   Full classes result in waiting list bookings
-   Students must cancel existing bookings before submitting a new booking request

---

## Documentation

-   [Use Cases](docs/UseCases.md)
-   [Functional Requirements](docs/FR.md)
-   [Technical Requirements](docs/TR.md)
-   [ERD Diagram](docs/diagrams/erd.md)

---

## Technology Stack

-   Backend: Laravel
-   Frontend: Blade + JavaScript
-   Database:
    -   SQLite (development & automated tests)
    -   MySQL (production-oriented design)
-   Testing: PHPUnit (planned)
-   API design: Documentation of RESTful endpoint design (TR.md)

---

## Notes

In the original system, booking data was also exported to Google Docs and administrators were notified via Slack.  
These integrations are documented but not fully implemented in this refactored version to keep the focus on core system design.

---

## Status

This project focuses on design-first implementation and iterative refinement.

---

## [Booking Flow](docs/diagrams/booking-flow.md)

```mermaid
flowchart TD
    A[Student enters booking page] --> B{Has active booking?}

    B -- Yes --> C[Prompt to cancel existing booking]
    C --> A

    B -- No --> D[View and filter class sessions<hr/>
    Subject, Center, Date range: 2 weeks]

    D --> E[Select class sessions<hr/>
    Validation applied:<br/>
    No time overlap<br/>
    No duplicate subject]

    E --> F[Review booking]
    F --> G[Submit booking]

    G --> H{Capacity available?}

    H -- Yes --> I[Create booking: confirmed]
    H -- No --> J[Create booking: waiting]

    I --> K[Send notifications]
    J --> K

    K --> L[End]

```

---

## [Booking Status Lifecycle](docs/diagrams/booking-state.md)

This document describes the lifecycle and state transitions of a booking entity.

```mermaid
stateDiagram-v2
    [*] --> Pending : Submit booking

    Pending --> Confirmed : Capacity available
    Pending --> Waiting : Capacity full

    Confirmed --> Cancelled : Student cancels booking
    Waiting --> Cancelled : Student cancels booking

    Cancelled --> [*]

```

---

## Screenshots

#### 1. Timetable with availability indicators
![Timetable](docs/screenshot/timetable.png)
The timetable visualises booking availability directly in the UI.

- Booked classes are marked
- Conflicts are highlighted
- Warnings are shown when the same subject is already booked

This helps users understand availability before submitting a booking.

#### 2. Booking Review Panel
![Booking Review](docs/screenshot/review.png)
Selected classes are displayed in a review panel before submission.
This allows users to quickly verify and modify their selections without navigating away from the page.

#### 3. My Bookings
![My Bookings](docs/screenshot/mybookings.png)
Users can view their confirmed bookings in a dedicated section, providing clear feedback after the booking process.

