## Booking Transaction Test Cases

### ✅ TC-00: Successful booking when capacity is available (Happy Path)

-   Given:
-   Student has no active booking
-   Class session has available capacity for the selected date
-   When:
-   Student submits a booking request for the class session and date
-   Then:
-   Booking is created successfully
-   Booking status is set to CONFIRMED

### ✅ TC-02: Successful booking across multiple centers

-   Given: Valid class sessions from different centers
-   When: Booking request is submitted
-   Then: Booking is accepted with travel-time warning shown
-   And: Status is CONFIRMED or WAITING depending on capacity

---

### ❌ TC-03: Duplicate active booking rejected

-   Given:
-   Student has an existing booking with status CONFIRMED or WAITING
-   Booking is for the same student (regardless of class/date)

-   When:
-   Student submits a new booking request

-   Then:
-   API responds with HTTP 409 Conflict
-   Response code is ACTIVE_BOOKING_EXISTS
-   No additional booking record is created in the database

### ❌ TC-04: Capacity exceeded results in waiting

-   Given: Class capacity is full
-   When: Booking request is submitted
-   Then: Booking is created with WAITING status

### ❌ TC-05: Waiting booking is promoted when a confirmed booking is cancelled

-   Given:
    Class session has:
    max_students = 1
    A confirmed booking already exists for the class session
    At least one waiting booking exists for the same class session
    The waiting booking has status WAITING
    The waiting booking was created earlier than others (oldest)
-   When:
    The confirmed booking is cancelled
-   Then:
    The cancelled booking status becomes CANCELLED
    The oldest waiting booking is:
    Promoted from WAITING → CONFIRMED
    No additional bookings are created
    Total number of CONFIRMED bookings remains within capacity
-   Notes (Business Rule):
    WAITING bookings do not consume capacity
    Promotion happens automatically when capacity becomes available
    Only one waiting booking is promoted per cancellation

### ❌ TC-06: Concurrent booking requests

-   Given: Capacity = 1
-   When: Two requests submit simultaneously
-   Then: One booking is CONFIRMED
-   And: The other is WAITING
