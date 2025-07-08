<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return view('login');
});

Route::post('/login', [AuthController::class, 'login']);



Route::get('/dashboard', [DashboardController::class, 'index']);

