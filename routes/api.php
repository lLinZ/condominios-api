<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatusController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::get('status', [StatusController::class, 'index']);
Route::post('status', [StatusController::class, 'create']);
Route::put('status/{status}', [StatusController::class, 'update']);

Route::get('roles', [RoleController::class, 'index']);
Route::post('roles', [RoleController::class, 'create']);
Route::put('roles/{role}', [RoleController::class, 'update']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user/data', [AuthController::class, 'get_logged_user_data']);
    Route::put('user/edit/{user}', [AuthController::class, 'edit_user']);
    Route::put('user/edit/{user}/color', [AuthController::class, 'edit_color']);

    // Ruta de registro de administrador de dominios
    Route::post('register/admin/condo', [AuthController::class, 'register_admin_de_condominios']);
    // Ruta de registro de cliente
    Route::post('register/client', [AuthController::class, 'register']);
    // Cerrar sesion
    Route::get('logout', [AuthController::class, 'logout']);
    // Roles
});
