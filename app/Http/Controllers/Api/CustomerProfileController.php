<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function store(Request $request)
    {
        $fields = $request->validate([
            'address'=>'required|max:255',
            'user_id'=>'required',
            'additional_info'=>'max:255',
        ]);

        $user = CustomerProfile::create($fields);
        
        return response()->json([
            'message'=>'Successfully Created your profile',
        ],200);
    }
}

