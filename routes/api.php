<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
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
Route::post('/registerEtudiant', [AuthController::class, 'registerEtudiant']);
Route::post('/registerEncadrant', [AuthController::class, 'registerEncadrant']);


// Route::middleware('auth:admin-api')->group(function () {
//     Route::get('/admin/dashboard', function (Request $request) {
//         return response()->json(['message' => 'Admin Dashboard', 'user' => $request->user()]);
        
//     Route::post('/add-etudiant', [AdminController::class, 'addEtudiant']);
//     Route::post('/add-encadrant', [AdminController::class, 'addEncadrant']);
//     });
// });
Route::middleware('auth:admin-api')->group(function () {
    Route::get('/admin/dashboard', function (Request $request) {
        return response()->json(['message' => 'Admin Dashboard', 'user' => $request->user()]);
    });

    Route::post('/add-etudiant', [AdminController::class, 'addEtudiant']);
    Route::put('/update-etudiant/{id}', [AdminController::class, 'updateEtudiant']);
    Route::delete('/delete-etudiant/{id}', [AdminController::class, 'deleteEtudiant']);
    Route::get('/list-etudiants', [AdminController::class, 'listEtudiants']);
    Route::get('/etudiant-infos/{id}', [AdminController::class, 'getEtudiant']);
  

    Route::post('/add-encadrant', [AdminController::class, 'addEncadrant']);
    Route::put('/update-encadrant/{id}', [AdminController::class, 'updateEncadrant']);
    Route::delete('/delete-encadrant/{id}', [AdminController::class, 'deleteEncadrant']);
    Route::get('/list-encadrants', [AdminController::class, 'listEncadrants']);
    Route::get('/encadrant-infos/{id}', [AdminController::class, 'getEncadrant']);


    Route::get('/admin-infos', [AdminController::class, 'getAdmin']);
    Route::put('/update-admin', [AdminController::class, 'updateAdmin']);


    Route::post('/add-sujets', [AdminController::class, 'addSujet']);
    Route::put('/update-sujet/{id}', [AdminController::class, 'updateSujet']);
    Route::delete('/delete-sujet/{id}', [AdminController::class, 'deleteSujet']);

    

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
