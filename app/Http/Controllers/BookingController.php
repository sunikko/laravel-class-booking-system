<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\BookingService;
use App\Models\Booking;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

            if ($bookingService->hasActiveBooking($student)) {
                return response()->json([
                    'code' => 'ACTIVE_BOOKING_EXISTS',
                ], 409);
            }

            $student = auth()->user()->student;
            if (! $student) {
                abort(403, 'Student profile not found');
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

    public function cancel(Booking $booking, BookingService $bookingService)
    {
        $bookingService->cancelBooking($booking);

        return response()->json([
            'message' => 'Booking cancelled successfully',
        ]);
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
