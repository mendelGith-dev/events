<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invite;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InviteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $invites = Invite::all();

            return response()->json([
                'success' => true,
                'message' => 'Invites retrieved successfully',
                'data' => $invites,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve invites',
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
            $validator = Validator::make($request->only(['name', 'email', 'event_id']), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'event_id' => 'required|exists:events,id',
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

            $invite = Invite::create($request->only(['name', 'email', 'event_id']));

            return response()->json([
                'success' => true,
                'message' => 'Invite created successfully',
                'data' => $invite,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to create invite',
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
            $invite = Invite::find($id);

            if (!$invite) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invite not found',
                    ],
                    404,
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Invite retrieved successfully',
                'data' => $invite,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve invite',
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
            $invite = Invite::find($id);

            if (!$invite) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invite not found',
                    ],
                    404,
                );
            }

            $validator = Validator::make($request->only(['name', 'email', 'event_id']), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'event_id' => 'sometimes|required|exists:events,id',
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

            $invite->update($request->only(['name', 'email', 'event_id']));

            return response()->json([
                'success' => true,
                'message' => 'Invite updated successfully',
                'data' => $invite,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update invite',
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
            $invite = Invite::find($id);

            if (!$invite) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invite not found',
                    ],
                    404,
                );
            }

            $invite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invite deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to delete invite',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function inviteToEventExcel(Request $request)
    {
        $validator = Validator::make($request->only(['file', 'event_id']), [
            'file' => 'required|file|mimes:xlsx,xls',
            'event_id' => 'required|exists:events,id',
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

        $event = Event::find($request->input('event_id'));

        $file = $request->file('file');

        try {
            $data = Excel::toArray(
                new class implements ToArray, WithHeadingRow {
                    public function array(array $array)
                    {
                        return $array;
                    }
                },
                $file,
            );

            $feuilleExcel = $data[0] ?? [];

            $invitesData = [];

            foreach ($feuilleExcel as $index => $row) {
                Invite::create([
                    'name' => $row['name'] ?? ($row['nom'] ??  null),
                    'email' => $row['email'] ?? ($row['courriel'] ??  ''),
                    'event_id' => $event->id,
                ]);
                //On en verra un email ici !
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to validate request',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }
}
