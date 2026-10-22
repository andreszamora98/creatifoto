<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhotoTypeController;
use App\Http\Controllers\PaperTypeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\AdditionalServiceController;
use App\Http\Controllers\BackgroundController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');

    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])
        ->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::view('/', 'layouts.app');
    Route::view('/cotizaciones', 'layouts.app');
    Route::view('/paquetes', 'layouts.app');
    Route::view('/catalogos', 'layouts.app');
    Route::view('/catalogos/caracteristicas-fotograficas', 'layouts.app');

    Route::get('/photo-types', [PhotoTypeController::class, 'index']);
    Route::post('/photo-types', [PhotoTypeController::class, 'store']);
    Route::put('/photo-types/{id}', [PhotoTypeController::class, 'update']);
    Route::delete('/photo-types/{id}', [PhotoTypeController::class, 'destroy']);

    Route::get('/paper-types', [PaperTypeController::class, 'index']);
    Route::post('/paper-types', [PaperTypeController::class, 'store']);
    Route::put('/paper-types/{paperType}', [PaperTypeController::class, 'update']);

    Route::get('/backgrounds', [BackgroundController::class, 'index']);
    Route::post('/backgrounds', [BackgroundController::class, 'store']);
    Route::put('/backgrounds/{background}', [BackgroundController::class, 'update']);

    Route::get('/customers', [CustomerController::class, 'index']);

    Route::get('/orders-table', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{order}/payments', [OrderController::class, 'addPayment']);
    Route::patch('/orders/{order}/deliver', [OrderController::class, 'deliver']);
    Route::patch('/orders/{order}/metadata', [OrderController::class, 'updateMetadata']);
    Route::patch('/orders/{order}/pickups', [OrderController::class, 'syncPickups']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);

    Route::patch('/customers/{customer}/contact', [CustomerController::class, 'updateContact']);

    Route::get('/quotes', [QuoteController::class, 'index']);
    Route::post('/quotes', [QuoteController::class, 'store']);
    Route::get('/quotes/{quote}', [QuoteController::class, 'show']);
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convert']);
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus']);
    Route::patch('/quotes/{quote}', [QuoteController::class, 'patch']);
    Route::put('/quotes/{quote}/concepts', [QuoteController::class, 'updateConcepts']);
    Route::put('/quotes/{quote}/customer', [QuoteController::class, 'updateCustomer'])
        ->name('quotes.updateCustomer');

    Route::get('/schools', [SchoolController::class, 'index']);
    Route::post('/schools', [SchoolController::class, 'store']);
    Route::put('/schools/{school}', [SchoolController::class, 'update']);
    Route::delete('/schools/{school}', [SchoolController::class, 'destroy']);

    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/packages/{package}', [PackageController::class, 'show']);
    Route::post('/packages', [PackageController::class, 'store']);
    Route::put('/packages/{package}', [PackageController::class, 'update']);
    Route::delete('/packages/{package}', [PackageController::class, 'destroy']);

    Route::get('/additional-services', [AdditionalServiceController::class, 'index']);
    Route::post('/additional-services', [AdditionalServiceController::class, 'store']);
    Route::put('/additional-services/{id}', [AdditionalServiceController::class, 'update']);
    Route::delete('/additional-services/{id}', [AdditionalServiceController::class, 'destroy']);
});