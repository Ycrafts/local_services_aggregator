<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;

class ProviderProfileController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'skills' => 'required|string',
            'experience_years' => 'required|integer',
            'rating' => 'nullable|numeric',
            'bio' => 'required|string',
            'location' => 'required|string',
            'job_type_ids' => 'required|array',
            'job_type_ids.*' => 'exists:job_types,id'
        ]);

        // Check if the user already has a provider profile
        if ($request->user()->providerProfile) {
            return response()->json([
                'message' => 'You already have a provider profile.'
            ], 400);
        }

        if ($request->user()->role !== 'provider') {
            return response()->json([
                'message' => 'Only providers can create provider profiles.'
            ], 403);
        }

        // Create the profile
        $profile = new ProviderProfile([
            'skills' => $validated['skills'],
            'experience_years' => $validated['experience_years'],
            'rating' => $validated['rating'] ?? 0,
            'bio' => $validated['bio'],
            'location' => $validated['location'],
        ]);

        $request->user()->providerProfile()->save($profile);

        // Attach job types (skills) via the pivot table
        $profile->jobTypes()->attach($validated['job_type_ids']);

        return response()->json([
            'profile' => $profile->load('jobTypes'),
            'message' => 'Provider profile created successfully with job types.'
        ], 201);
    }

    public function update(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'skills' => 'required|string',
            'experience_years' => 'required|integer',
            'rating' => 'nullable|numeric',
            'bio' => 'required|string',
            'location' => 'required|string',
            'job_type_ids' => 'nullable|array',
            'job_type_ids.*' => 'integer|exists:job_types,id',
        ]);

        // Get the existing profile for the current user
        $profile = $request->user()->providerProfile;

        if (!$profile) {
            return response()->json([
                'message' => 'No provider profile found.',
            ], 404);
        }

        // Update the profile fields
        $profile->update($validated);

        // Sync job types if provided
        if ($request->has('job_type_ids')) {
            $profile->jobTypes()->sync($request->job_type_ids);
        }

        // Return updated profile with job types
        return response()->json([
            'profile' => $profile->load('jobTypes'),
            'message' => 'Profile updated successfully.',
        ]);
    }

    /**
     * Update provider payout details
     */
    public function updatePayoutDetails(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Only providers can update payout details.'], 403);
        }

        $request->validate([
            'bank_account_number' => 'required|string',
            'bank_code' => 'required|string',
            'bank_name' => 'required|string',
        ]);

        $user->bank_account_number = $request->bank_account_number;
        $user->bank_code = $request->bank_code;
        $user->bank_name = $request->bank_name;
        $user->save();

        return response()->json(['message' => 'Bank details updated successfully.']);
    }
}
