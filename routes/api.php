<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:admin-api')->group(function () {
    Route::get('/admin/dashboard', function (Request $request) {
        return response()->json(['message' => 'Admin Dashboard', 'user' => $request->user()]);
    });
});

Route::middleware('auth:etudiant-api')->group(function () {
    Route::get('/etudiant/dashboard', function (Request $request) {
        return response()->json(['message' => 'Etudiant Dashboard', 'user' => $request->user()]);
    });
});

Route::middleware('auth:encadrant-api')->group(function () {
    Route::get('/encadrant/dashboard', function (Request $request) {
        return response()->json(['message' => 'Encadrant Dashboard', 'user' => $request->user()]);
    });
});
