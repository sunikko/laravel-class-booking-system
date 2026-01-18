<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">

    <h1 class="text-2xl font-bold mb-4">Class Booking Portal</h1>

    <div id="statusMsg" class="mb-4 text-sm text-red-600"></div>

    <!-- ===== Sessions ===== -->
    <h2 class="text-xl font-semibold mb-2">Available Classes</h2>

    <table class="min-w-full bg-gray-50 border rounded mb-8">
        <thead class="bg-gray-200">
        <tr>
            <th class="border px-3 py-2">Class</th>
            <th class="border px-3 py-2">Subject</th>
            <th class="border px-3 py-2">Schedule</th>
            <th class="border px-3 py-2">Status</th>
            <th class="border px-3 py-2">Action</th>
        </tr>
        </thead>
        <tbody id="sessionTable"></tbody>
    </table>

    <!-- ===== My Bookings ===== -->
    <h2 class="text-xl font-semibold mb-2">My Bookings</h2>

    <table class="min-w-full bg-gray-50 border rounded">
        <thead class="bg-gray-200">
        <tr>
            <th class="border px-3 py-2">ID</th>
            <th class="border px-3 py-2">Session</th>
            <th class="border px-3 py-2">Session Start Date</th>
            <th class="border px-3 py-2">Bookiing Date</th>
            <th class="border px-3 py-2">Booking Status</th>
        </tr>
        </thead>
        <tbody id="bookingTable" class="text-center"></tbody>
    </table>

</div>

<h2 class="text-xl font-semibold mb-4">Weekly Schedule</h2>

<div class="grid grid-cols-5 gap-4" id="calendar"></div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const state = {
    sessions: [],
    bookings: [],
};

/* ================= API ================= */

async function fetchSessions() {
    const res = await fetch('/bookings/sessions');
    state.sessions = await res.json();
}

async function fetchBookings() {
    const res = await fetch('/bookings/api');
    state.bookings = await res.json();
}

async function bookSession(sessionId) {
    try {
        const res = await fetch('/bookings/api', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                class_session_id: sessionId,
                booking_date: new Date().toISOString().slice(0, 10)
            })
        });

        if (!res.ok) {
            const err = await res.json();
            throw new Error(err.code || 'Booking failed');
        }

        document.getElementById('statusMsg').innerText = 'Booking successful!';
        await init();
    } catch (e) {
        document.getElementById('statusMsg').innerText = e.message;
    }
}

/* ================= Render ================= */
function formatSchedule(session) {
    const date = new Date(session.start_date).toLocaleDateString();
    const day = session.day_of_week;
    const time = session.start_time;
    const duration = session.duration_min;
    
    return `${date} (${day}) ${time} / ${duration} mins`;
}

function renderSessionTable() {
    const bookedMap = {};
    state.bookings.forEach(b => {
        bookedMap[b.class_session_id] = b.status;
    });

    const tbody = document.getElementById('sessionTable');
    tbody.innerHTML = '';

    state.sessions.forEach(session => {
        const bookingStatus = bookedMap[session.id] ?? 'available';
        const canBook = bookingStatus === 'available';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="border px-3 py-2">${session.class_name}</td>
            <td class="border px-3 py-2">${session.class_subject}</td>
            <td class="border px-3 py-2">${formatSchedule(session)}</td>
            <td class="border px-3 py-2">${bookingStatus}</td>
            <td class="border px-3 py-2 text-center">
                ${canBook
                    ? `<button
                        onclick="bookSession(${session.id})"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                        Book
                      </button>`
                    : `<span class="text-gray-400">-</span>`
                }
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderBookingTable() {
    const tbody = document.getElementById('bookingTable');
    tbody.innerHTML = '';

    state.bookings.forEach(booking => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="border px-3 py-2">${booking.id}</td>
            <td class="border px-3 py-2">${booking.class_session?.class_name ?? 'N/A'}</td>
            <td class="border px-3 py-2">${formatSchedule(booking.class_session)}</td>
            <td class="border px-3 py-2">${new Date(booking.booking_date).toLocaleDateString()}</td>
            <td class="border px-3 py-2">${booking.status}</td>
        `;
        tbody.appendChild(tr);
    });
}

/* ================= Calendar ================= */
const DAYS = [
    { key: 'monday', label: 'Monday' },
    { key: 'tuesday', label: 'Tuesday' },
    { key: 'wednesday', label: 'Wednesday' },
    { key: 'thursday', label: 'Thursday' },
    { key: 'friday', label: 'Friday' },
];

function getNextTwoWeeks() {
    const days = [];
    const today = new Date();

    for (let i = 0; i < 14; i++) {
        const d = new Date(today);
        d.setDate(today.getDate() + i);

        // Mon–Fri만
        if (d.getDay() >= 1 && d.getDay() <= 5) {
            days.push(d);
        }
    }
    return days;
}

function getBookedSessionIds() {
    return new Set(
        state.bookings
            .filter(b => b.status === 'confirmed')
            .map(b => b.class_session_id)
    );
}

function getConfirmedBookingMap() {
    const map = {};
    state.bookings
        .filter(b => b.status === 'confirmed')
        .forEach(b => {
            map[b.class_session_id] = true;
        });
    return map;
}

function renderSessionsIntoCalendar(days) {
    const bookedSessionIds = getBookedSessionIds();
    const hasBookingThisWeek = state.bookings.some(
        b => b.status === 'confirmed'
    );

    days.forEach(date => {
        const dateKey = date.toISOString().slice(0, 10);
        const dayOfWeek = date
            .toLocaleDateString('en-GB', { weekday: 'long' })
            .toLowerCase(); // monday

        const container = document.getElementById(`day-${dateKey}`);
        if (!container) return;

        state.sessions
            .filter(s => s.day_of_week === dayOfWeek)
            .forEach(s => {
                const isFull = s.booked_count >= s.max_students;
                const isBooked = bookedSessionIds.has(s.id);
                const disabled = isFull || isBooked || hasBookingThisWeek;

                const card = document.createElement('div');
                card.className = `
                    p-2 border rounded text-sm
                    ${isBooked ? 'bg-blue-100' : isFull ? 'bg-gray-200' : 'bg-green-100'}
                `;

                card.innerHTML = `
                    <div class="font-semibold">${s.class_name}</div>
                    <div>${s.start_time} (${s.duration_min}m)</div>
                    <div class="text-xs mt-1">
                        ${s.booked_count} / ${s.max_students}
                        ${isFull ? '<span class="text-red-500 ml-1">FULL</span>' : ''}
                    </div>
                    ${
                        isBooked
                            ? `<div class="mt-2 text-blue-700 text-xs font-semibold">Booked</div>`
                            : disabled
                                ? `<button class="mt-2 w-full bg-gray-400 text-white py-1 rounded" disabled>
                                     Not available
                                   </button>`
                                : `<button
                                       onclick="bookSession(${s.id})"
                                       class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white py-1 rounded">
                                       Book
                                   </button>`
                    }
                `;

                container.appendChild(card);
            });
    });
}

function renderCalendar() {
    const container = document.getElementById('calendar');
    container.innerHTML = '';

    const days = getNextTwoWeeks();

    days.forEach(date => {
        const dateKey = date.toISOString().slice(0, 10); // YYYY-MM-DD
        const dayName = date.toLocaleDateString('en-GB', { weekday: 'short' });

        const col = document.createElement('div');
        col.className = 'border rounded p-2';

        col.innerHTML = `
            <div class="font-bold text-center">
                ${dayName}<br/>
                <span class="text-xs">${dateKey}</span>
            </div>
            <div class="space-y-2 mt-2" id="day-${dateKey}"></div>
        `;

        container.appendChild(col);
    });

    renderSessionsIntoCalendar(days);
}



/* ================= Init ================= */

async function init() {
    await Promise.all([
        fetchSessions(),
        fetchBookings()
    ]);

    renderSessionTable();
    renderBookingTable();
    renderCalendar();
}

init();
</script>

</body>
</html>
