# Functional Requirements

## Authentication

**FR-01**  
Only authenticated students can access the booking system.

---

## Class Discovery

**FR-02**  
Students can view class sessions that match their grade.

**FR-03**  
Each class session displays subject, center, schedule, and availability.

**FR-04**  
Students can filter class sessions by subject and center.

---

## Booking Constraints

**FR-05**  
Bookings are allowed only within a defined two-week window.

**FR-06**  
The system prevents overlapping time bookings and duplicate subject bookings.

**FR-11**  
Students may select class sessions across multiple centers, with a reminder to consider travel time between centers.

**FR-16**  
Students with existing active bookings must cancel them before submitting a new booking request.

---

## Booking Review

**FR-07**  
A booking preview is provided before confirmation, including conflicts and waiting status.

---

## Booking Submission & Validation

**FR-08**  
Availability is validated at confirmation time.

**FR-09**  
The system guarantees capacity limits under concurrent requests.

**FR-14**  
The system automatically assigns a booking status of `waiting` if capacity is exceeded at submission time.

**FR-15**  
The system informs the student of the final booking status after submission.

---

## Persistence & Notifications

**FR-10**  
Incomplete booking data is not permanently stored.

**FR-12**  
Booking data is persisted only after the final submission step.

**FR-13**  
Upon successful booking submission, the system sends notifications to administrators and confirmation emails to the student.
