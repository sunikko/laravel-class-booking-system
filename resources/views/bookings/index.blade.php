<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">

    <h1 class="text-2xl font-bold mb-4">Class Booking Portal</h1>

    <div id="statusMsg" class="mb-4 text-sm text-red-600"></div>

    <h2 class="text-xl font-semibold mb-2">Available Classes</h2>

    <table class="min-w-full bg-gray-50 border rounded mb-6">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-3 py-2">Class</th>
                <th class="border px-3 py-2">Subject</th>
                <th class="border px-3 py-2">Start</th>
                <th class="border px-3 py-2">Status</th>
                <th class="border px-3 py-2">Action</th>
            </tr>
        </thead>
        <tbody id="sessionTable"></tbody>
    </table>


    <label for="sessionSelect" class="block mb-2 font-medium">Select a class session:</label>
    <select id="sessionSelect" class="border rounded p-2 w-full mb-4"></select>

    <button id="bookBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
        Book
    </button>

    <table class="min-w-full bg-gray-50 border rounded">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-2 px-4 border">ID</th>
                <th class="py-2 px-4 border">Session</th>
                <th class="py-2 px-4 border">Start</th>
                <th class="py-2 px-4 border">End</th>
                <th class="py-2 px-4 border">Status</th>
            </tr>
        </thead>
        <tbody id="bookingTable" class="text-center"></tbody>
    </table>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;


document.getElementById('bookBtn').addEventListener('click', async () => {
    const select = document.getElementById('sessionSelect');
    const sessionId = select.value;
    if (!sessionId) return;

    try {
        const res = await fetch('/bookings/api', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                class_session_id: sessionId,
                booking_date: new Date().toISOString().slice(0,10)
            })
        });

        if (!res.ok) {
            const err = await res.json();
            document.getElementById('statusMsg').innerText = err.code || 'Booking failed';
        } else {
            document.getElementById('statusMsg').innerText = 'Booking successful!';
        }

        await loadBookings();
    } catch(e) {
        console.error(e);
        document.getElementById('statusMsg').innerText = 'Error connecting to server';
    }
});

async function fetchSessions() {
    const res = await fetch('/bookings/sessions');
    if (!res.ok) return;

    const sessions = await res.json();
    const select = document.getElementById('sessionSelect');
    select.innerHTML = '';

    sessions.forEach(s => {
        if (s.status === 'active') {
            const option = document.createElement('option');
            option.value = s.id;
            option.text = `${s.class_name} (${new Date(s.start_time).toLocaleString()} - ${new Date(s.end_at).toLocaleTimeString()})`;
            select.appendChild(option);
        }
    });
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
            document.getElementById('statusMsg').innerText =
                err.code || 'Booking failed';
            return;
        }

        document.getElementById('statusMsg').innerText =
            'Booking successful!';

        await loadUI();  
        await loadBookings();
    } catch (e) {
        console.error(e);
        document.getElementById('statusMsg').innerText =
            'Error connecting to server';
    }
}


async function loadUI() {
    const [sessions, bookings] = await Promise.all([
        fetch('/bookings/sessions').then(r => r.json()),
        fetch('/bookings/api').then(r => r.json()),
    ]);

    const bookedMap = {};
    bookings.forEach(b => {
        bookedMap[b.class_session_id] = b.status;
    });

    const tbody = document.getElementById('sessionTable');
    tbody.innerHTML = '';

    sessions.forEach(s => {
        const status = bookedMap[s.id] ?? 'available';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="border px-3 py-2">${s.class_name}</td>
            <td class="border px-3 py-2">${s.class_subject}</td>
            <td class="border px-3 py-2">${new Date(s.start_date).toLocaleDateString()}(${s.day_of_week}) - ${s.start_time} : ${s.duration_min} mins</td>
            <td class="border px-3 py-2">${status}</td>
            <td class="border px-3 py-2">
                ${status === 'available'
                    ? `<button class="bg-blue-500 text-white px-2 py-1 rounded" onclick="bookSession(${s.id})">Book</button>`
                    : `<span class="text-gray-500">-</span>`
                }
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function loadBookings() {
    try {
        const res = await fetch('/bookings/api');
        const data = await res.json();

        const tbody = document.getElementById('bookingTable');
        tbody.innerHTML = '';

        let hasActiveBooking = false;

        data.forEach(b => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-2 px-4 border">${b.id}</td>
                <td class="py-2 px-4 border">${b.class_name}</td>
                <td class="py-2 px-4 border">${new Date(b.start_at).toLocaleString()}</td>
                <td class="py-2 px-4 border">${new Date(b.end_at).toLocaleString()}</td>
                <td class="py-2 px-4 border">${b.status}</td>
            `;
            tbody.appendChild(tr);

            if (b.status === 'confirmed') hasActiveBooking = true;
        });

        const btn = document.getElementById('bookBtn');
        if (hasActiveBooking) {
            btn.disabled = true;
            btn.innerText = 'Already booked';
        } else {
            btn.disabled = false;
            btn.innerText = 'Book';
        }

        await fetchSessions();
    } catch(e) {
        console.error('Failed to load bookings', e);
    }
}

loadBookings();
loadUI();
</script>

</body>
</html>
