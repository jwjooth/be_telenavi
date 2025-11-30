<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;

Route::middleware('web')->group(function () {
    Route::post('/api/todos', [TodoController::class, 'store'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    Route::get('/api/todos', [TodoController::class, 'index']);
    Route::get('/api/todos/export', [TodoController::class, 'export']);
    Route::get('/', function () {
        return view('welcome');
    });
});
