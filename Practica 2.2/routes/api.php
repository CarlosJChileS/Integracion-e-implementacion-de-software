<?php

use App\Http\Controllers\Api\EnrollmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->controller(EnrollmentController::class)->group(function () {
    Route::get('/enrollments', 'index');
    Route::post('/enrollments', 'store');
    Route::get('/enrollments/{enrollment}', 'show');
    Route::patch('/enrollments/{enrollment}', 'update');
    Route::delete('/enrollments/{enrollment}', 'destroy');
});
