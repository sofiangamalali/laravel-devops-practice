<?php

use App\Http\Controllers\CrashController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
Route::get('/notes', [NoteController::class, 'index']);
Route::post('/notes', [NoteController::class, 'store']);
Route::get('/stats', StatsController::class);
Route::get('/crash', CrashController::class);
