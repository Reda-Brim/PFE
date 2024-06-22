<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\EncadrantController;
use App\Http\Controllers\API\EtudiantController;
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
    Route::get('/list-etudiants-sans-equipe', [AdminController::class, 'listEtudiantsSansEquipe']);

  

    Route::post('/add-encadrant', [AdminController::class, 'addEncadrant']);
    Route::put('/update-encadrant/{id}', [AdminController::class, 'updateEncadrant']);
    Route::delete('/delete-encadrant/{id}', [AdminController::class, 'deleteEncadrant']);
    Route::get('/list-encadrants', [AdminController::class, 'listEncadrants']);
    Route::get('/encadrant-infos/{id}', [AdminController::class, 'getEncadrant']);


    Route::get('/admin-infos', [AdminController::class, 'getAdmin']);
    Route::put('/update-admin', [AdminController::class, 'updateAdmin']);

    Route::get('/admin/statistics', [AdminController::class, 'getStatistics']);


    Route::get('/list-sujets', [AdminController::class, 'listSujets']);
    Route::post('/add-sujets', [AdminController::class, 'addSujet']);
    Route::put('/update-sujet/{id}', [AdminController::class, 'updateSujet']);
    Route::delete('/delete-sujet/{id}', [AdminController::class, 'deleteSujet']);
    Route::get('/sujet-infos/{id}', [AdminController::class, 'getSujetInfos']);


    Route::post('/createequipe', [AdminController::class, 'createEquipe']);
    Route::put('/assignencadrant/{id}', [AdminController::class, 'assignEncadrant']);
    Route::put('/update-equipe/{id}', [AdminController::class, 'updateEquipe']);
    Route::get('/equipe-Infos/{id}', [AdminController::class, 'equipeInfos']);


    Route::get('/list-equipes', [AdminController::class, 'listEquipes']);
    Route::post('/list-equipes/{equipe}/addmembre', [AdminController::class, 'addMemberToEquipe']);
    Route::delete('/list-equipes/{equipe}/deletemembre', [AdminController::class, 'removeMemberFromEquipe']);
    Route::delete('/list-equipes/{id}', [AdminController::class, 'deleteEquipe']);

});

Route::middleware('auth:etudiant-api')->group(function () {
    Route::get('/etudiant/dashboard', function (Request $request) {
        return response()->json(['message' => 'Etudiant Dashboard', 'user' => $request->user()]);
    });
    Route::get('/etudiant/sujet-assigne', [EtudiantController::class, 'getAssignedSubject']);
    Route::get('/etudiant/taches-assignees', [EtudiantController::class, 'getAssignedTasks']);
    Route::get('/etudiant/equipe-members', [EtudiantController::class, 'getEquipeMembers']);

    Route::post('/etudiant/change-password', [EtudiantController::class, 'changePassword']);

    Route::put('/etudiant/taches/{tacheId}', [EtudiantController::class, 'updateTache']);




});

Route::middleware('auth:encadrant-api')->group(function () {
    Route::get('/encadrant/dashboard', function (Request $request) {
        return response()->json(['message' => 'Encadrant Dashboard', 'user' => $request->user()]);
    });
    Route::get('/encadrant/equipes', [EncadrantController::class, 'listEquipes']);
    Route::get('/encadrant/sujets', [EncadrantController::class, 'listSujets']);
    Route::post('/encadrant/add-sujets', [EncadrantController::class, 'addSujet']);
    Route::post('/encadrant/equipes/{equipe}/assign-sujet', [EncadrantController::class, 'assignSujetToEquipe']);

    Route::post('/encadrant/projets/{projetId}/taches', [EncadrantController::class, 'addTacheToProjet']);
    Route::put('/encadrant/taches/{tacheId}', [EncadrantController::class, 'updateTache']);
    Route::delete('/encadrant/taches/{tacheId}', [EncadrantController::class, 'deleteTache']);


});
