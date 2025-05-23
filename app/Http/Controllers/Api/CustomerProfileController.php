<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 

class CustomerProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user(); 
        $profile = CustomerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json([
            'profile' => $profile->load('user'),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|max:255',
            'additional_info' => 'nullable|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user(); 

        if (CustomerProfile::where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Profile already exists'], 400);
        }

        $profile = CustomerProfile::create([
            'user_id' => $user->id,
            'address' => $request->address,
            'additional_info' => $request->additional_info,
        ]);

        return response()->json([
            'message' => 'Profile created successfully',
            'profile' => $profile->load('user'),
        ], 201);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|max:255',
            'additional_info' => 'nullable|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $profile = CustomerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $profile->update([
            'address' => $request->address,
            'additional_info' => $request->additional_info,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile->load('user'),
        ]);
    }
}