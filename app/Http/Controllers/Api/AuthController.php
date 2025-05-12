<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $startTime = microtime(true);
        
        // Request processing timing
        $requestStart = microtime(true);
        $requestData = $request->all();
        $requestTime = microtime(true) - $requestStart;
        Log::info("Request processing time: {$requestTime} seconds");
        
        // Validation timing
        $validationStart = microtime(true);
        $fields = $request->validate([
            'name'=>'required|max:255',
            'role'=>'required',
            'email'=>'email|unique:users',
            'phone_number' => [
                'required',
                'unique:users',
                'regex:/^(\+251|0)?9\d{8}$/'
            ],
            'password'=>'required|confirmed'
        ]);
        $validationTime = microtime(true) - $validationStart;
        Log::info("Validation time: {$validationTime} seconds");

        // User creation timing
        $creationStart = microtime(true);
        $user = User::create($fields);
        $creationTime = microtime(true) - $creationStart;
        Log::info("User creation time: {$creationTime} seconds");
        
        // Response preparation timing
        $responseStart = microtime(true);
        $response = response()->json([
            'message'=>'Successfully Registered',
            'debug' => [
                'request_time' => $requestTime,
                'validation_time' => $validationTime,
                'creation_time' => $creationTime,
                'total_time' => microtime(true) - $startTime
            ]
        ],200);
        $responseTime = microtime(true) - $responseStart;
        Log::info("Response preparation time: {$responseTime} seconds");
        
        $totalTime = microtime(true) - $startTime;
        Log::info("Total registration time: {$totalTime} seconds");
        
        return $response;
    }

    public function login(Request $request){
        $request->validate([
            'phone_number'=>'required',
            'password' => 'required'
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
            return response()->json([
                'message'=>'Unknown phone number or wrong password',
            ],403);
        }

        $token = $user->createToken($user->id);

        return [
            'token'=>$token->plainTextToken
        ];
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'message'=>'You are logged out'
        ],200);
    }
}
