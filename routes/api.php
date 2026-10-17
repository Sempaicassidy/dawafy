<?php

use App\Http\Controllers\POSController;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| Daawafy API Routes (React JS & jQuery REST API Endpoints)
|--------------------------------------------------------------------------
*/

Route::get('/pos/search', [POSController::class, 'searchApi']);
Route::get('/pos/scan', [POSController::class, 'scanApi']);
Route::post('/pos/checkout', [POSController::class, 'checkoutApi']);

Route::get('/inventory/drugs', [InventoryController::class, 'listApi']);
