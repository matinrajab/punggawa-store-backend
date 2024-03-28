<?php

use App\Http\Controllers\API\AddressCategoryController;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\OpenAIController;
use App\Http\Controllers\API\PaymentMethodController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\TransactionStatusController;
use App\Http\Controllers\API\UserController;
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

Route::get('products', [ProductController::class, 'all']);
Route::get('product-categories', [ProductCategoryController::class, 'all']);
Route::get('address-categories', [AddressCategoryController::class, 'all']);
Route::get('payment-methods', [PaymentMethodController::class, 'all']);

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::post('midtrans-callback', [TransactionController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::get('addresses', [AddressController::class, 'all']);
    Route::post('address', [AddressController::class, 'store']);
    Route::put('address/{id}', [AddressController::class, 'update'])->middleware('address.owner');
    Route::delete('address/{id}', [AddressController::class, 'delete'])->middleware('address.owner');

    Route::get('transactions', [TransactionController::class, 'all']);
    Route::post('checkout', [TransactionController::class, 'checkout']);
    Route::post('top-up', [TransactionController::class, 'topup']);
    Route::put('transaction-status/{id}', [TransactionStatusController::class, 'update'])->middleware('transaction.owner');
    Route::put('bonus', [TransactionController::class, 'addBonus']);
});

Route::post('chat', [OpenAIController::class, 'chat']);
