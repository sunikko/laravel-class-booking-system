<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Models\ClassSession;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('bookings.index');
    });
    Route::get('/bookings', fn() => view('bookings.index'))->name('bookings.index');

    Route::get('/bookings/api', [BookingController::class, 'index']);
    Route::post('/bookings/api', [BookingController::class, 'store']);

    Route::get('/bookings/sessions', function () {
        return response()->json(
            ClassSession::withCount([
                'bookings as booked_count' => function ($q) {
                    $q->where('status', 'confirmed');
                }
            ])->get()
        );
    });
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
