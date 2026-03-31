<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\ClassSession;
use App\DataTransferObjects\ClassSessionData;
use Illuminate\Http\Request;
use App\Enums\BookingStatus;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Booking React Page Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/bookings', function () {
        $classSessions = ClassSession::withCount([
            'bookings as booked_count' => function ($q) {
                $q->where('status', 'confirmed');
            }
        ])->get();
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');

        $sessionData = ClassSessionData::fromCollection($classSessions);

        // 사용자의 Student 정보를 먼저 가져온 뒤, 예약 내역을 가져옵니다.
        $student = auth()->user()->student;
        $bookings = $student ? $student->bookings()->with('classSession')->get() : collect();

        return Inertia::render('Bookings/Index', [
            'sessions' => $sessionData,
            'bookings' => $bookings
        ]);
    })->name('bookings.index');

    Route::post('/bookings', [BookingController::class, 'store']);
});

require __DIR__ . '/auth.php';
