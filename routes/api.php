<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CondominiumController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\UnitTypeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UnitController;

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




Route::middleware('auth:sanctum')->group(function () {

    // Crear condominium
    Route::post('condominium', [CondominiumController::class, 'create']);

    // Obtener condominios
    Route::get('condominium', [CondominiumController::class, 'index']);

    // Obtener condominios
    Route::get('condominium/close/{condominium}', [CondominiumController::class, 'close_condominium']);

    // Obtener condominios
    Route::get('condominium/cancel/{condominium}', [CondominiumController::class, 'cancel_condominium']);

    // Obtener usuarios
    Route::get('users', [AuthController::class, 'get_all_users']);

    // Registrar pago
    Route::post('payment', [PaymentController::class, 'create']);

    // Obtener pagos
    Route::get('payment', [PaymentController::class, 'index']);

    // Obtener pagos
    Route::put('payment/decline/{payment}', [PaymentController::class, 'decline']);

    // Obtener pagos
    Route::put('payment/approve/{payment}', [PaymentController::class, 'approve']);

    // Obtener pagos pendientes
    Route::get('payments/pending', [PaymentController::class, 'get_pending_payments']);

    // Obtener unidades del usuario logeado
    Route::get('user/units', [UnitController::class, 'get_units']);

    // Obtener data del user logeado
    Route::get('user/data', [AuthController::class, 'get_logged_user_data']);

    // Editar usuario
    Route::put('user/edit/{user}', [AuthController::class, 'edit_user']);

    // Cambiar color del usuario logeado
    Route::put('user/edit/{user}/color', [AuthController::class, 'edit_color']);

    // Obtener divisa
    Route::get('currency', [CurrencyController::class, 'index']);

    // Crear divisa
    Route::post('currency', [CurrencyController::class, 'create']);

    // Obtener edificios
    Route::get('buildings', [BuildingController::class, 'index']);

    // Crear edificio
    Route::post('buildings', [BuildingController::class, 'create']);

    // Mapear un edificio
    Route::post('buildings/mapping', [BuildingController::class, 'mapping']);

    // Obtener tipos de unidad de un edificio
    Route::get('buildings/unit_types/{building}', [UnitTypeController::class, 'get_unit_types_by_building']);

    // Registrar tipo de unidades
    Route::post('buildings/unit_types/{building}', [UnitTypeController::class, 'create']);

    // Editar tipo de unidad
    Route::put('buildings/unit_types/{building}/{unit_type}', [UnitTypeController::class, 'edit']);

    // Asignar tipo de unidad
    Route::post('unit/{unit}/unit_type/{unit_type}', [UnitController::class, 'asign_unit_type']);

    // Asignar owner
    Route::post('unit/{unit}/user/{user}', [UnitController::class, 'asign_owner']);

    // Obtener unidades por edificios
    Route::get('buildings/units/{building}', [UnitController::class, 'get_units_by_building']);

    // Ruta de registro de administrador de dominios
    Route::post('register/admin/condo', [AuthController::class, 'register_admin_de_condominios']);

    // Ruta de registro de cliente
    Route::post('register/client', [AuthController::class, 'register']);

    // Ruta de registro de master
    Route::post('register/master', [AuthController::class, 'register_master']);

    // Cerrar sesion
    Route::get('logout', [AuthController::class, 'logout']);
    // Roles
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'create']);
    Route::put('roles/{role}', [RoleController::class, 'update']);
});
