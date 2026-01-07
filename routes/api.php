<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::middleware('auth')->post('/bookings', [BookingController::class, 'store']);
