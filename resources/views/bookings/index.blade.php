<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Booking Timetable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .time-slot {
            min-height: 120px;
        }

        .class-card {
            transition: all 0.2s;
        }

        .class-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">

        <h1 class="text-3xl font-bold mb-6">Class Booking Timetable</h1>
        <div id="statusMsg" class="mb-4 text-sm hidden"></div>

        <!-- Selected Bookings Summary -->
        <div id="selectedSummary" class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4 hidden">
            <h3 class="font-semibold mb-2">Selected Bookings:</h3>
            <div id="selectedList" class="text-sm mb-3"></div>
            <button id="bookBtn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                Book Selected Classes
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-3 w-24 sticky left-0 bg-gray-100">Time</th>
                        <th class="border p-3">Mon</th>
                        <th class="border p-3">Tue</th>
                        <th class="border p-3">Wed</th>
                        <th class="border p-3">Thu</th>
                        <th class="border p-3">Fri</th>
                        <th class="border p-3">Sat</th>
                    </tr>
                </thead>
                <tbody id="timetableBody"></tbody>
            </table>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">My Bookings</h2>
            <div id="bookingsList"></div>
        </div>

    </div>

    <script>
        /* ================= GLOBAL ================= */
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const state = {
            sessions: [],
            bookings: [],
            selectedBookings: []
        };

        /* ================= CONFIG ================= */
        const TIME_SLOTS = ["10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"];
        const WEEKDAYS = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];

        const DAY_NUMBER_MAP = {
            1: 'monday',
            2: 'tuesday',
            3: 'wednesday',
            4: 'thursday',
            5: 'friday',
            6: 'saturday',
            7: 'sunday'
        };

        const COLORS = {
            english: "bg-blue-500",
            math: "bg-pink-500",
            default: "bg-gray-500"
        };

        /* ================= NORMALIZE ================= */
        function normalizeDay(day) {
            if (day == null) return null;
            const num = Number(day);
            if (!isNaN(num)) return DAY_NUMBER_MAP[num] ?? null;
            return day.toLowerCase().trim();
        }

        const normalizeTime = t => t?.slice(0, 5);

        /* ================= API ================= */
        async function fetchSessions() {
            const res = await fetch('/bookings/sessions');
            state.sessions = await res.json();
        }

        async function fetchBookings() {
            const res = await fetch('/bookings/api');
            state.bookings = await res.json();
        }

        async function bookSession(id, date) {
            const res = await fetch('/bookings/api', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    class_session_id: id,
                    booking_date: date
                })
            });
            if (!res.ok) throw new Error((await res.json()).message);
        }

        async function bookMultiple(bookings) {
            const errors = [];
            for (const {
                    id,
                    date
                }
                of bookings) {
                try {
                    await bookSession(id, date);
                } catch (err) {
                    errors.push(`${id}: ${err.message}`);
                }
            }
            return errors;
        }

        /* ================= HELPERS ================= */
        const subjectColor = s => COLORS[s?.toLowerCase()] || COLORS.default;

        const isBooked = id =>
            state.bookings.some(b => b.class_session_id === id && b.status === 'confirmed');

        function hasConflict(session) {
            return state.bookings.some(b => {
                if (b.status !== 'confirmed') return false;
                const s = state.sessions.find(x => x.id === b.class_session_id);
                if (!s) return false;
                return normalizeDay(s.day_of_week) === normalizeDay(session.day_of_week) &&
                    normalizeTime(s.start_time) === normalizeTime(session.start_time);
            });
        }

        function nextDates(session, count = 2) {
            const day = normalizeDay(session.day_of_week);
            if (!day) return [];

            let d = new Date();
            const target = Number(Object.entries(DAY_NUMBER_MAP)
                .find(([k, v]) => v === day)?.[0]);

            while (d.getDay() !== target) d.setDate(d.getDate() + 1);

            return Array.from({
                length: count
            }, () => {
                const copy = new Date(d);
                d.setDate(d.getDate() + 7);
                return copy;
            });
        }

        /* ================= UI ================= */
        function createCard(session) {
            const booked = isBooked(session.id);
            const conflict = hasConflict(session);
            const full = session.booked_count >= session.max_students;

            const bookedSubjects = new Set(
                state.bookings
                .filter(b => b.status === 'confirmed')
                .map(b => state.sessions.find(s => s.id === b.class_session_id)?.class_subject)
                .filter(Boolean)
            );
            const subjectBooked = bookedSubjects.has(session.class_subject);

            const card = document.createElement('div');
            card.className = `class-card ${subjectColor(session.class_subject)} text-white rounded p-3 mb-2 text-sm`;

            const dates = nextDates(session);
            const bookedDates = state.bookings
                .filter(b => b.class_session_id === session.id && b.status === 'confirmed')
                .map(b => b.booking_date);

            card.innerHTML = `
        <div class="font-bold">${session.class_subject || 'Class'}</div>
        <div class="text-xs opacity-80">${session.class_name}</div>

        <div class="mt-2 text-xs space-y-1">
            ${dates.map(d => {
                const dateStr = d.toISOString().slice(0,10);
                const bookedDatesSet = new Set(bookedDates);
                const isDateBooked = bookedDatesSet.has(dateStr);
                
                return `
                <label class="flex items-center gap-1">
                    ${(!conflict && !full && !isDateBooked && !subjectBooked)
                        ? `<input type="radio" name="session${session.id}" class="booking-radio" data-id="${session.id}" data-date="${dateStr}" data-subject="${session.class_subject || 'Class'}" data-classname="${session.class_name}">`
                        : '<span class="w-4"></span>'}
                    ${d.toLocaleDateString()}
                    ${isDateBooked ? '<span class="ml-1 font-bold">✓</span>' : ''}
                </label>
            `}).join('')}
        </div>

        <div class="mt-2 text-xs font-semibold">
            ${subjectBooked ? '✓ SUBJECT BOOKED' : booked ? '✓ BOOKED' : full ? 'FULL' : conflict ? '⚠ CONFLICT' : ''}
        </div>
    `;
            return card;
        }

        function renderTimetable() {
            const tbody = document.getElementById('timetableBody');
            tbody.innerHTML = '';

            TIME_SLOTS.forEach(time => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td class="border p-2 sticky left-0 bg-gray-50 font-semibold">${time}</td>`;

                WEEKDAYS.forEach(day => {
                    const td = document.createElement('td');
                    td.className = 'border p-2 align-top';

                    state.sessions
                        .filter(s =>
                            normalizeDay(s.day_of_week) === day &&
                            normalizeTime(s.start_time) === time
                        )
                        .forEach(s => td.appendChild(createCard(s)));

                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });

            // Add checkbox event listeners
            document.querySelectorAll('.booking-radio').forEach(rb => {
                rb.addEventListener('change', handleRadioChange);
            });
        }

        function handleRadioChange(e) {
            if (!e.target.checked) return;

            const selectedSubject = e.target.dataset.subject;

            document.querySelectorAll('.booking-radio').forEach(radio => {
                if (radio !== e.target && radio.dataset.subject === selectedSubject) {
                    radio.checked = false;
                }
            });

            updateSelectedSummary();
        }

        function updateSelectedSummary() {
            const radios = document.querySelectorAll('.booking-radio:checked');
            const summary = document.getElementById('selectedSummary');
            const list = document.getElementById('selectedList');

            if (radios.length === 0) {
                summary.classList.add('hidden');
                state.selectedBookings = [];
                return;
            }

            summary.classList.remove('hidden');

            const selected = Array.from(radios).map(rb => {
                return {
                    id: rb.dataset.id,
                    date: rb.dataset.date,
                    subject: rb.dataset.subject,
                    className: rb.dataset.classname
                };
            });

            list.innerHTML = selected.map(s => `
                <div class="py-1">• ${s.subject} (${s.className}) - ${new Date(s.date).toLocaleDateString()}</div>
            `).join('');

            state.selectedBookings = selected;
        }

        function renderBookings() {
            const el = document.getElementById('bookingsList');
            el.innerHTML = state.bookings.length ?
                state.bookings.map(b => `
            <div class="flex justify-between p-3 bg-gray-50 rounded mb-2">
                <span>${b.class_session?.class_subject || ''} - ${b.class_session?.class_name} (${b.booking_date})</span>
                <span class="text-xs bg-green-100 text-green-700 px-2 rounded">${b.status}</span>
            </div>
        `).join('') :
                '<p class="text-sm text-gray-500">No bookings yet</p>';
        }

        function showMessage(msg, type = 'error') {
            const el = document.getElementById('statusMsg');
            el.textContent = msg;
            el.className = `mb-4 text-sm ${type==='success'?'text-green-600':'text-red-600'}`;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 3000);
        }

        /* ================= INIT ================= */
        async function init() {
            await Promise.all([fetchSessions(), fetchBookings()]);
            renderTimetable();
            renderBookings();

            // Add book button event listener
            const bookBtn = document.getElementById('bookBtn');
            if (bookBtn) {
                bookBtn.onclick = async () => {
                    if (state.selectedBookings.length === 0) return;

                    const errors = await bookMultiple(state.selectedBookings);

                    if (errors.length === 0) {
                        showMessage(`✓ Successfully booked ${state.selectedBookings.length} class(es)`, 'success');
                    } else {
                        showMessage(`Partial success. Errors: ${errors.join(', ')}`);
                    }

                    state.selectedBookings = [];
                    await init();
                };
            }
        }
        init();
    </script>
</body>

</html>