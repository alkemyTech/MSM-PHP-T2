<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankMovementsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::middleware('auth:api')->group(function () {

    Route::prefix('accounts')->group(function () {
        Route::get('balance', [BankMovementsController::class, 'index']); 
        Route::patch('{account_id}', [AccountController::class,'updateAccountLimit']);
    });

    Route::get('transactions', [AccountController::class, 'index']);

    Route::post('fixed_terms', [BankMovementsController::class, 'create']);
    Route::post('accounts', [AccountController::class, 'create']);
    Route::post('/transactions/payment', [BankMovementsController::class, 'payment']);
    Route::post('transactions/send', [BankMovementsController::class, 'send']);

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->withoutMiddleware('auth:api');
        Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth:api');
    });

    Route::middleware('role:ADMIN')->get('users', [UserController::class, 'index']);

    Route::delete('/users/{id}', [UserController::class,'deleteUser']);

    Route::patch('/transactions/{transaction_id}', [BankMovementsController::class,'updateTransaction']);

});
