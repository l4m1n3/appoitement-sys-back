<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ServiceController,
    PosteController,
    EmployeController,
    BadgeController,
    PortiqueController,
    PointageController,
    AuthController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. 
| Routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);



    // -----------------------------
    // Routes personnalisées Pointage
    // -----------------------------
    Route::get('pointages/present', [PointageController::class, 'present']);
    Route::get('pointages/employe/{id}', [PointageController::class, 'historique'])->whereNumber('id');
    Route::get('pointages/employe/{id}/dernier', [PointageController::class, 'dernierPointage'])->whereNumber('id');
    Route::get('pointages/portique/{id}', [PointageController::class, 'parPortique'])->whereNumber('id');
    Route::get('pointages/presence', [PointageController::class, 'presenceParJour']);
    // Endpoint pour les MAC autorisés
    Route::get('portiques/authorized', [PointageController::class, 'authorizedPortiques']);
    // -----------------------------
    // Routes APIResource (CRUD)
    // -----------------------------
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('postes', PosteController::class);
    Route::apiResource('employes', EmployeController::class);
    Route::apiResource('badges', BadgeController::class);
    Route::apiResource('portiques', PortiqueController::class);
    Route::apiResource('pointages', PointageController::class)->whereNumber('pointage');
});
// -----------------------------
// Optionnel : info utilisateur
// -----------------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
