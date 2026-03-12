<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Invite;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $events = Event::all();

            return response()->json([
                'success' => true,
                'message' => 'Events retrieved successfully',
                'data' => $events,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve events',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validator = Validator::make($request->only(['title', 'description', 'location', 'date', 'time']), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ],
                    422,
                );
            }

            $event = Event::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'date' => $request->date,
                'time' => $request->time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $event,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to create event',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $event = Event::with('invites')->find($id);

            if (!$event) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Event not found',
                    ],
                    404,
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Event retrieved successfully',
                'data' => $event,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve event',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Event not found',
                    ],
                    404,
                );
            }

            $validator = Validator::make($request->only(['title', 'description', 'location', 'date', 'time']), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'date' => 'sometimes|required|date',
                'time' => 'sometimes|required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ],
                    422,
                );
            }

            $event->update($request->only(['title', 'description', 'location', 'date', 'time']));

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $event,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update event',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Event not found',
                    ],
                    404,
                );
            }

            $event->invites()->delete();
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to delete event',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }
}
