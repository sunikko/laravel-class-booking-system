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

        .class-card.disabled {
            pointer-events: none;
        }

        .class-card.disabled:hover {
            transform: none;
        }

        .booking-radio:disabled {
            cursor: not-allowed;
            opacity: 0.4;
        }
    </style>
</head>

<body class="bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">

        <h1 class="text-3xl font-bold mb-6">Class Booking Timetable</h1>
        <div id="statusMsg" class="mb-4 text-sm hidden"></div>

        <!-- Selected Bookings Summary -->
        <div id="selectedSummary" class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4 hidden">
            <h3 class="font-semibold mb-3">Selected Bookings:</h3>
            <div id="selectedList" class="text-sm mb-4 space-y-2"></div>
            <div class="flex gap-2">
                <button id="bookBtn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                    Book Selected Classes
                </button>
                <button id="cancelAllBtn" class="bg-white text-red-600 border border-red-600 px-6 py-2 rounded hover:bg-red-50 transition">
                    Cancel All
                </button>
            </div>
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

        const ERROR_MESSAGES = {
            3: 'already booked this class session on the selected date.',
            8: 'same time slot is already booked.'
        };

        const COLORS = {
            english: "border-l-4 border-sky-400",
            math: "border-l-4 border-rose-400",
            science: "border-l-4 border-emerald-400",
            default: "border-l-4 border-slate-300"
        };

        const CARD_STATE_STYLE = {
            booked: 'bg-gray-200 opacity-60',
            conflict: 'bg-yellow-50 opacity-60',
            full: 'bg-gray-100 opacity-70',
            normal: 'bg-white'
        };

        const STATUS_LABEL = {
            booked: '✓ BOOKED',
            conflict: '⚠ CONFLICT',
            subject: '⚠ SAME SUBJECT BOOKED',
            full: 'FULL'
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
            if (!res.ok) {
                const data = await res.json();
                const msg = ERROR_MESSAGES[data.code] ?? data.message ?? 'Unknown error';
                throw new Error(msg);
            }
        }

        async function bookMultiple(bookings) {
            const errorCodes = new Set();
            for (const {
                    id,
                    date
                }
                of bookings) {
                try {
                    await bookSession(id, date);
                } catch (err) {
                    if (err.message.includes('already booked')) errorCodes.add('duplicate');
                    else if (err.message.includes('same time')) errorCodes.add('conflict');
                    else errorCodes.add('unknown');
                }
            }

            return Array.from(errorCodes);
        }

        /* ================= HELPERS ================= */
        const subjectColor = s => COLORS[s?.toLowerCase()] || COLORS.default;

        // check if a session is already booked (optionally on a specific date)
        function isSessionBooked(sessionId, date = null) {
            return state.bookings.some(b =>
                b.class_session_id == sessionId &&
                b.status === 'CONFIRMED' &&
                (!date || b.booking_date === date)
            );
        }

        // check time conflict with existing bookings
        function hasConflict(session) {
            return state.bookings.some(b => {
                if (b.status !== 'CONFIRMED') return false;
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

        function getCardState(session) {
            const anyDateBooked = isSessionBooked(session.id);
            const conflict = hasConflict(session);
            const full = session.booked_count >= session.max_students;

            const bookedSubjects = new Set(
                state.bookings
                .filter(b => b.status === 'CONFIRMED')
                .map(b => state.sessions.find(s => s.id === b.class_session_id)?.class_subject)
                .filter(Boolean)
            );
            const subjectBooked = bookedSubjects.has(session.class_subject);

            const isDisabled = anyDateBooked || conflict || full;

            let styleClass = CARD_STATE_STYLE.normal;
            if (anyDateBooked || conflict) {
                styleClass = CARD_STATE_STYLE.booked;
            } else if (full) {
                styleClass = CARD_STATE_STYLE.full;
            }

            let statusMessage = '';
            if (anyDateBooked) statusMessage = STATUS_LABEL.booked;
            else if (conflict) statusMessage = STATUS_LABEL.conflict;
            else if (subjectBooked) statusMessage = STATUS_LABEL.subject;
            else if (full) statusMessage = STATUS_LABEL.full;

            return {
                anyDateBooked,
                conflict,
                full,
                subjectBooked,
                isDisabled,
                styleClass,
                statusMessage
            };
        }

        /* ================= UI ================= */
        function createCard(session) {
            const cardState = getCardState(session);
            const dates = nextDates(session);

            const card = document.createElement('div');
            card.className = `
                class-card text-gray-800 
                ${subjectColor(session.class_subject)}
                rounded p-3 mb-2 text-sm shadow-sm
                ${cardState.styleClass}
                ${cardState.isDisabled ? 'disabled' : ''}
            `;

            card.innerHTML = `
                <div class="font-bold">${session.class_subject || 'Class'}</div>
                <div class="text-xs opacity-80">${session.class_name}</div>

                <div class="mt-2 text-xs space-y-1">
                    ${dates.map(d => {
                        const dateStr = d.toISOString().slice(0, 10);
                        const isThisDateBooked = isSessionBooked(session.id, dateStr);
                        
                        const canBook = !cardState.isDisabled && !isThisDateBooked;
                        
                        return `
                            <label class="flex items-center gap-1 ${!canBook ? 'text-gray-400' : ''}">
                                <input 
                                    type="radio"
                                    name="session${session.id}"
                                    class="booking-radio"
                                    ${canBook ? '' : 'disabled'}
                                    ${canBook ? `
                                        data-id="${session.id}"
                                        data-date="${dateStr}"
                                        data-day="${normalizeDay(session.day_of_week)}"
                                        data-time="${normalizeTime(session.start_time)}"
                                        data-subject="${session.class_subject || 'Class'}"
                                        data-classname="${session.class_name}"
                                    ` : ''}
                                >

                                ${d.toLocaleDateString()}
                                ${isThisDateBooked ? '<span class="ml-1 font-bold">✓</span>' : ''}
                            </label>
                        `;
                    }).join('')}
                </div>

                <div class="mt-2 text-xs font-semibold">
                    ${cardState.statusMessage}
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

                    const sessions = state.sessions.filter(s =>
                        normalizeDay(s.day_of_week) === day &&
                        normalizeTime(s.start_time) === time
                    );

                    if (sessions.length === 0) {
                        td.innerHTML = `<p class="text-xs text-gray-400 italic">No class</p>`;
                    } else {
                        sessions.forEach(s => td.appendChild(createCard(s)));
                    }


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

            const selectedSessionId = e.target.dataset.id;
            const selectedDate = e.target.dataset.date;
            const selectedTime = e.target.dataset.time;

            // Uncheck other radios of the same session
            document.querySelectorAll('.booking-radio').forEach(radio => {
                if (radio !== e.target && radio.dataset.id === selectedSessionId) {
                    radio.checked = false;
                }
            });

            // Uncheck any other bookings at the same date and time
            document.querySelectorAll('.booking-radio').forEach(radio => {
                if (radio !== e.target &&
                    radio.dataset.date === selectedDate &&
                    radio.dataset.time === selectedTime) {
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

            const selected = Array.from(radios).map(rb => ({
                id: rb.dataset.id,
                date: rb.dataset.date,
                subject: rb.dataset.subject,
                className: rb.dataset.classname
            }));

            list.innerHTML = selected.map((s, index) => `
                <div class="flex items-center justify-between py-2 px-3 bg-white rounded border border-blue-200">
                    <span>• ${s.subject} (${s.className}) - ${new Date(s.date).toLocaleDateString()}</span>
                    <button 
                        class="remove-booking-btn text-red-600 hover:text-red-800 ml-4 text-xs px-2 py-1 border border-red-300 rounded hover:bg-red-50 transition"
                        data-id="${s.id}"
                        data-date="${s.date}"
                    >
                        Remove
                    </button>
                </div>
            `).join('');

            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-booking-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = e.target.dataset.id;
                    const date = e.target.dataset.date;

                    // Remove from selected array
                    document.querySelectorAll('.booking-radio').forEach(radio => {
                        if (radio.dataset.id === id && radio.dataset.date === date) {
                            radio.checked = false;
                        }
                    });

                    // Update the summary
                    updateSelectedSummary();
                });
            });

            state.selectedBookings = selected;
        }

        function renderBookings() {
            const el = document.getElementById('bookingsList');
            el.innerHTML = state.bookings.length ?
                state.bookings.map(b => `
                    <div class="flex justify-between p-3 bg-gray-50 rounded mb-2">
                        <span>${b.class_session?.class_subject || ''} - ${b.class_session?.class_name} ${b.class_session?.start_time} (${new Date(b.booking_date).toLocaleDateString()})</span>
                        <span class="text-xs bg-green-100 text-green-700 px-2 rounded">${b.status}</span>
                    </div>
                `).join('') :
                '<p class="text-sm text-gray-500">No bookings yet</p>';
        }

        function showMessage(msg, type = 'error') {
            const el = document.getElementById('statusMsg');
            el.textContent = msg;
            el.className = `mb-4 text-sm ${type === 'success' ? 'text-green-600' : 'text-red-600'}`;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 3000);
        }

        /* ================= INIT ================= */
        async function init() {
            await Promise.all([fetchSessions(), fetchBookings()]);
            renderTimetable();
            renderBookings();

            const bookBtn = document.getElementById('bookBtn');
            if (bookBtn) {
                bookBtn.onclick = async () => {
                    if (state.selectedBookings.length === 0) return;

                    bookBtn.disabled = true;
                    const originalText = bookBtn.textContent;
                    bookBtn.textContent = 'Booking...';

                    try {
                        const errors = await bookMultiple(state.selectedBookings);

                        if (errors.length === 0) {
                            showMessage(`✓ Successfully booked ${state.selectedBookings.length} class(es)`, 'success');
                        } else {
                            const messages = [];

                            if (errors.includes('duplicate'))
                                messages.push('Some classes were already booked');
                            if (errors.includes('conflict'))
                                messages.push('Some classes had time conflicts');
                            if (errors.includes('unknown'))
                                messages.push('Some bookings failed');

                            showMessage(`Partial success. ${messages.join('. ')}`);
                        }

                        state.selectedBookings = [];
                        await init();
                    } finally {
                        bookBtn.disabled = false;
                        bookBtn.textContent = originalText;
                    }
                };

            }

            const cancelAllBtn = document.getElementById('cancelAllBtn');
            if (cancelAllBtn) {
                cancelAllBtn.onclick = () => {
                    // Uncheck all radio buttons
                    document.querySelectorAll('.booking-radio:checked').forEach(radio => {
                        radio.checked = false;
                    });

                    // Clear selected bookings
                    state.selectedBookings = [];

                    // Hide summary
                    document.getElementById('selectedSummary').classList.add('hidden');
                };
            }
        }
        init();
    </script>
</body>

</html>