# Use Cases – Class Booking System

## UC-01: Student views available class sessions

**Actor**  
Student

**Description**  
A student views all available class sessions that match their grade.

**Preconditions**

- The student is authenticated.
- The student profile contains grade information.

**Main Flow**

1. The student accesses the booking page.
2. The system retrieves all class sessions available for the student’s grade.
3. For each class session, the system determines availability based on capacity.
4. The system displays class sessions with their current booking status.

**Postconditions**

- The student can see which classes are available, full, or waiting-listed.

---

## UC-02: Student filters classes by subject and center

**Actor**  
Student

**Description**  
A student filters class sessions by subject and preferred center.

**Main Flow**

1. The student selects one or more subjects.
2. The student selects a center (offline or online).
3. The system updates the visible class sessions accordingly.

**Notes**

- Filtering is performed on the client side using preloaded data.
- Availability status is always based on server-side calculation.

---

## UC-03: Student selects classes for booking

**Actor**  
Student

**Description**  
A student selects one or more class sessions within a two-week booking window.

**Preconditions**

- The student is authenticated.
- The student does not have an existing active booking batch.
- Available class sessions have been loaded.

**Main Flow**

1. The system checks whether the student has an existing active booking batch.
2. If an active booking exists, the student is prompted to cancel it before proceeding
3. The system displays class sessions for a two-week period.
4. The student selects desired class sessions.
5. The student may select classes across multiple centers.
6. The system prevents:
   - Booking the same subject multiple times.
   - Booking overlapping class times.
7. If a class is full, the selection is marked as waiting.

**Postconditions**

- Selected classes are temporarily held in the client state for review.

**Notes**

- No booking data is persisted until the final submission step.

---

## UC-04: Student reviews booking before submission

**Actor**  
Student

**Description**  
The student reviews selected class sessions before final submission.

**Main Flow**

1. The system displays a booking summary.
2. The system calculates an estimated total cost.
3. The student enters optional comments or discount codes.
4. The student confirms agreement with booking terms.

---

## UC-05: Student submits booking request

**Actor**  
Student

**Description**  
The student submits the booking request for final validation.

**Main Flow**

1. The student submits the booking form.
2. The system validates availability again to handle concurrent bookings.
3. The system creates booking records with appropriate status.
4. The system stores booking data in the database.
5. Confirmation emails are sent to the student and administrators.
6. Booking details are sent to Google Docs for administrative reference.
7. The system sends notifications to administrators via Slack.

**Postconditions**

- Booking records are created with status `confirmed` or `waiting`.
- No payment is processed at this stage.

## UC-06: Student cancels existing booking

**Actor**  
Student

**Description**  
The student cancels an existing booking before submitting a new one.

**Main Flow**

1. The student views existing bookings.
2. The student cancels the active booking batch.
3. The system updates booking statuses to `cancelled`.
4. The system releases reserved capacity.
