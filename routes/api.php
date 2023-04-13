<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PurchaseController;

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

Route::controller(AuthController::class)->group(function(){
    Route::post('/register', 'createAccount');
    Route::post('/login', 'loginAccount');
});

Route::group([], function(){
    Route::get('/getUser/{id}', [UserController::class, 'GetUser']);
    Route::get('/wallet-history/{id}', [WalletController::class, 'WalletHistory']);
    Route::get('/list-categories', [CategoryController::class, 'listCategory']);
    Route::get('/delete-plan/{id}', [PlanController::class, 'deletePlan']);
});

Route::controller(TestController::class)->group(function(){
    Route::get('/test', 'index');
});

Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::post('/generate-virtual-account', [UserController::class, 'GenerateUserVirtualAccount']);
    Route::put('/modify-user-password', [UserController::class, 'ModifyUserPassword']);
    Route::put('/upgrade-plan', [UserController::class, 'UpgradePlan']);

    Route::post('/create-wallet-request', [WalletController::class, 'createWalletRequest']);
    Route::post('/transfer-fund', [WalletController::class, 'TransferFund']);

    Route::post('/buy-airtime', [PurchaseController::class, 'PurchaseAirtime']);
});