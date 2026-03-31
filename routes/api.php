<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Models\ClassSession;
use App\DataTransferObjects\ClassSessionData;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    Route::get('/bookings/sessions', function () {
        $classSessions = ClassSession::withCount([
            'bookings as booked_count' => function ($q) {
                $q->where('status', 'confirmed');
            }
        ])->get();

        $sessionData = ClassSessionData::fromCollection($classSessions);

        return response()->json($sessionData);
    });
});
