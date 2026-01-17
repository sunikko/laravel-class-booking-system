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

/* ================= Init ================= */

async function init() {
    await Promise.all([
        fetchSessions(),
        fetchBookings()
    ]);

    renderSessionTable();
    renderBookingTable();
}

init();
</script>

</body>
</html>
