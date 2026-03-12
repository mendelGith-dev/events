<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InviteController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);
Route::resource('users', UserController::class)->except(['index']);
Route::get('/users', [UserController::class, 'index'])->middleware('jwt.custom');
Route::resource('events', EventController::class)->middleware('jwt.custom');
Route::resource('invites', InviteController::class)->middleware('jwt.custom');

Route::post('/logout', [UserController::class, 'logout'])->middleware('jwt.custom');