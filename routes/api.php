<?php

use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\EnrollmentEventController;
use App\Http\Controllers\Api\NormalizeEnrollmentPayloadController;
use App\Http\Controllers\Api\SolicitudSummaryController;
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

Route::middleware('auth:sanctum')->post('/enrollment-events', EnrollmentEventController::class)
    ->name('enrollment-events.route');

Route::post('/integrations/enrollments/normalize', NormalizeEnrollmentPayloadController::class)
    ->middleware('auth:sanctum');

Route::get('/resumen-solicitud/{solicitudId}', SolicitudSummaryController::class)->whereNumber('solicitudId');
