<?php

use App\Http\Controllers\ProductController;
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

Route::middleware(['auth:sanctum', 'ability:api:product'])->group(function () {
    Route::get('/product', [ProductController::class, 'index']);

    Route::get('/product/{id}', [ProductController::class, 'show'])
        ->whereNumber('id');

    Route::put('/product/{id}', [ProductController::class, 'update'])
        ->whereNumber('id');
});
