<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AQIController;


Route::get('/', [AQIController::class, 'index'])->name('home');
Route::post('/upload', [AQIController::class, 'upload'])->name('upload');
Route::get('/download', [AQIController::class, 'download'])->name('download');
Route::post('/save_messages', [AQIController::class, 'saveMessages'])->name('save_messages');
