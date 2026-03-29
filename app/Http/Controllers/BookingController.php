<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\BookingService;
use App\Models\Booking;
use App\DataTransferObjects\BookingData;


class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if (! $user || ! $user->student) {
            return response()->json([]);
        }
        return Booking::with('classSession')
            ->where('student_id', $user->student->id)
            ->get();
        // Eager load classSession for the DTO
        $bookings = Booking::with('classSession') // Make sure 'classSession' relationship is loaded
            ->where('student_id', $user->student->id)
            ->get();

        // You will need a BookingData DTO to transform this collection
        // Assuming you have already created app/DataTransferObjects/BookingData.php
        // and it uses ClassSessionData internally for the classSession relationship.
        $bookingData = BookingData::fromCollection($bookings);

        return response()->json($bookingData);
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
