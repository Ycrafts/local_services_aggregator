<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    public function register(Request $request)
    {
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
        $user = User::create($fields);
        
        return response()->json([
            'message'=>'Successfully Registered',
        ],200);
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
