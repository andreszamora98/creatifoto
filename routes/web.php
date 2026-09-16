<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoTypeController;
use App\Http\Controllers\PaperTypeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('layouts/app');
});

/*
 * Catálogos
 */
Route::get('/photo-types', [PhotoTypeController::class, 'index']);
Route::post('/photo-types', [PhotoTypeController::class, 'store']);
Route::put('/photo-types/{id}', [PhotoTypeController::class, 'update']);
Route::delete('/photo-types/{id}', [PhotoTypeController::class, 'destroy']);

Route::get('/paper-types', [PaperTypeController::class, 'index']);
Route::get('/packages', [PackageController::class, 'index']);
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
