# Database Schema

This document describes the database tables and relationships used in the booking system.

---

## students

| Column               | Type      | Notes       |
| -------------------- | --------- | ----------- |
| id                   | bigint    | Primary key |
| name                 | string    |             |
| email                | string    | Unique      |
| grade                | string    |             |
| preferred_subjects   | json      | Optional    |
| preferred_centers    | json      | Optional    |
| preferred_time_slots | json      | Optional    |
| created_at           | timestamp |             |

---

## class_sessions

| Column       | Type      | Notes            |
| ------------ | --------- | ---------------- |
| id           | bigint    | Primary key      |
| subject      | string    |                  |
| center       | string    |                  |
| teacher_id   | bigint    | FK → teachers.id |
| start_time   | datetime  |                  |
| end_time     | datetime  |                  |
| max_students | int       |                  |
| price        | decimal   |                  |
| created_at   | timestamp |                  |

---

## bookings

| Column           | Type      | Notes                           |
| ---------------- | --------- | ------------------------------- |
| id               | bigint    | Primary key                     |
| student_id       | bigint    | FK → students.id                |
| class_session_id | bigint    | FK → class_sessions.id          |
| status           | string    | confirmed / waiting / cancelled |
| comment          | text      | Optional                        |
| created_at       | timestamp |                                 |

---

## Constraints

- A booking must reference an existing student and class session
- A student may have only one active booking batch
- Capacity is enforced via transaction logic, not database constraints

---

## Relationships

- Student 1:N Booking
- ClassSession 1:N Booking
