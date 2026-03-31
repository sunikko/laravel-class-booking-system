<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\BookingService;
use App\Models\Booking;
use App\DataTransferObjects\BookingData;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     * 1. retrieve data using the service layer
     * 2. return JSON response if it's an API request, otherwise render Inertia view for web requests
     */
    public function index(Request $request, BookingService $bookingService)
    {
        $data = $bookingService->getIndexData($request->user());

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($data);
        }
        return Inertia::render('Bookings/Index', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, BookingService $bookingService)
    {
        try {
            $user = auth()->user();
            $student = $user->student;

            if (! $student) {
                return response()->json([
                    'code' => 'STUDENT_NOT_FOUND',
                ], 403);
            }

            $bookingService->createBooking(
                $student,
                $request->input('class_session_id'),
                $request->input('booking_date')
            );

            return response()->json([
                'message' => 'Booking created',
            ], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return response()->json([
                'code' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking, BookingService $bookingService)
    {
        $bookingService->cancelBooking($booking);

        return response()->noContent();
    }
}
