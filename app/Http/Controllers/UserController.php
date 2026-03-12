<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = User::all();

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve users',
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
            $validator = Validator::make($request->only(['name', 'email', 'password', 'password_confirmation']), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|confirmed|min:8',
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

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to create user',
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
            if (!$id) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User ID is required',
                    ],
                    400,
                );
            }
            $user = User::find($id);

            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User not found',
                    ],
                    404,
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve user',
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
            $validator = Validator::make($request->only(['name', 'email']), [
                'name' => 'string|max:255',
                'email' => 'string|email|max:255|unique:users,email,' . $id,
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

            $user = User::find($id);

            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User not found',
                    ],
                    404,
                );
            }

            $user->update($request->only(['name', 'email']));

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update user',
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
            $user = User::find($id);

            if (!$user) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'User not found',
                    ],
                    404,
                );
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to delete user',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->only(['email', 'password']), [
                'email' => 'required|string|email',
                'password' => 'required',
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

            $credentials = $request->only('email', 'password');

            $user = User::where('email', $credentials['email'])->first();

            if (! $user || ! $token = JWTAuth::attempt($credentials)) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invalid email or password',
                    ],
                    401,
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to login',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function logout()
    {
        try {
            auth()->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to logout',
                    'error' => $th->getMessage(),
                ],
                500,
            );
        }
    }
}
