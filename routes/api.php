<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{name}', [CountryController::class, 'show']);
Route::post('/countries', [CountryController::class, 'store']);
Route::delete('/countries/{name}', [CountryController::class, 'destroy']);
Route::post('/countries/refresh', [CountryController::class, 'refresh']);
Route::get('/countries/image', [CountryController::class, 'summaryImage']);
Route::get('/status', [CountryController::class, 'status']);
