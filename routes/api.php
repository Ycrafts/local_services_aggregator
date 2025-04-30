<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController; // Add the JobController import
// use App\Http\Controllers\JobTypeController;
use App\Http\Controllers\Api\ProviderProfileController;


// Authentication Routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


// Job Routes
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('jobs', [JobController::class, 'store']);  

    // Route::post('express-interest', [JobController::class, 'expressInterest']);  

    Route::get('jobs', [JobController::class, 'index']); 

    Route::get('jobs/{job}', [JobController::class, 'show']);  
    Route::middleware('auth:sanctum')->post('/provider-profile', [ProviderProfileController::class, 'store']);

    Route::middleware('auth:sanctum')->put('/provider-profile', [ProviderProfileController::class, 'update']);

    // // Optional: Route to update a job (if customer wants to update a job)
    // Route::put('jobs/{job}', [JobController::class, 'update']);  // PUT /jobs/{job} - Update a job

    // // Optional: Route to delete a job (if needed)
    // Route::delete('jobs/{job}', [JobController::class, 'destroy']);  // DELETE /jobs/{job} - Delete a job
});

Route::middleware('auth:sanctum')->post('/jobs/{jobId}/express-interest', [JobController::class, 'expressInterest']);
Route::get('/jobs/{job}/interested-providers', [JobController::class, 'interestedProviders'])->middleware('auth:sanctum');
Route::post('/jobs/{job}/select-provider', [JobController::class, 'selectProvider'])->middleware('auth:sanctum');
Route::get('/requested-jobs', [JobController::class, 'providerRequestedJobs'])->middleware('auth:sanctum');
