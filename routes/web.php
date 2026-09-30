<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoTypeController;
use App\Http\Controllers\PaperTypeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SchoolController;


Route::get('/', function () {
    return view('layouts/app');
});

Route::get('/cotizaciones', function () {
    return view('layouts/quotes');
});

Route::get('/paquetes', function () {
    return view('layouts/packages');
});

/*
 * Catálogos
 */
Route::get('/photo-types', [PhotoTypeController::class, 'index']);
Route::post('/photo-types', [PhotoTypeController::class, 'store']);
Route::put('/photo-types/{id}', [PhotoTypeController::class, 'update']);
Route::delete('/photo-types/{id}', [PhotoTypeController::class, 'destroy']);

Route::get('/paper-types', [PaperTypeController::class, 'index']);
Route::get('/customers', [CustomerController::class, 'index']);

/*
 * Órdenes
 */
Route::get('/orders-table', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/orders/{order}/payments', [OrderController::class, 'addPayment']);
Route::patch('/orders/{order}/deliver', [OrderController::class, 'deliver']);
Route::get('/orders/{order}', [OrderController::class, 'show']);

//Clientes
Route::patch('/customers/{customer}/contact', [CustomerController::class, 'updateContact']);

//Cotizaciones
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

Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/{package}', [PackageController::class, 'show']);
Route::post('/packages', [PackageController::class, 'store']);
Route::put('/packages/{package}', [PackageController::class, 'update']);
Route::delete('/packages/{package}', [PackageController::class, 'destroy']);