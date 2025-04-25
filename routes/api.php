<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController; // Add the JobController import

// Authentication Routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Job Routes
Route::middleware('auth:sanctum')->group(function () {
    // Route to post a job (Customer posting a job)
    Route::post('jobs', [JobController::class, 'store']);  // POST /jobs - Create a new job

    // Route to get all jobs
    Route::get('jobs', [JobController::class, 'index']);  // GET /jobs - Fetch all jobs

    // Route to get a specific job
    Route::get('jobs/{job}', [JobController::class, 'show']);  // GET /jobs/{job} - Fetch a specific job

    // // Optional: Route to update a job (if customer wants to update a job)
    // Route::put('jobs/{job}', [JobController::class, 'update']);  // PUT /jobs/{job} - Update a job

    // // Optional: Route to delete a job (if needed)
    // Route::delete('jobs/{job}', [JobController::class, 'destroy']);  // DELETE /jobs/{job} - Delete a job
});
