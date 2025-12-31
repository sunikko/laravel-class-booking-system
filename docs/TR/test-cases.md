## Booking Transaction Test Cases

### TC-01: Student with active booking cannot submit new booking

- Given: Student has confirmed booking
- When: Submit new booking request
- Then: API returns 409 ACTIVE_BOOKING_EXISTS

### TC-02: Capacity exceeded results in waiting status

- Given: Class capacity full
- When: Booking submitted
- Then: Booking created with WAITING status

### TC-03: Concurrent booking requests for last slot

- Given: Capacity = 1
- When: Two transactions submit simultaneously
- Then: One CONFIRMED, one WAITING

### TC-04: Partial invalid class selection

- Given: One valid, one conflicting session
- When: Booking submitted
- Then: Request rejected, no booking persisted
