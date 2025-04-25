<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\RequestedJob;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;


class JobController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_type_id'     => 'required|exists:job_types,id',
            'description'     => 'required|string',
            'proposed_price'  => 'required|numeric|min:1',
        ]);

        if ($request->user()->role !== 'customer') {
            return response()->json(['error' => 'Only customers can post jobs.'], 403);
        }

        $job = Job::create([
            'user_id'        => $request->user()->id,
            'job_type_id'    => $validated['job_type_id'],
            'description'    => $validated['description'],
            'proposed_price' => $validated['proposed_price'],
            'status'         => 'open',
        ]);

        $matchedProviders = ProviderProfile::whereHas('jobTypes', function ($query) use ($job) {
            $query->where('job_type_id', $job->job_type_id);
        })->get();
    
        foreach ($matchedProviders as $provider) {
            RequestedJob::create([
                'job_id' => $job->id,
                'provider_profile_id' => $provider->id,
            ]);
            // Optional: send notification to provider (email, SMS, etc.)
        }
    
        return response()->json([
            'message' => 'Job posted successfully.',
            'job'     => $job
        ], 201);

    }

    public function index()
    {
        $jobs = Job::where('user_id', Auth::id())->paginate(10);  // 10 jobs per page

        return response()->json($jobs);
    }

    public function show($id)
    {
        $job = Job::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found or not authorized.'], 404);
        }

        return response()->json($job);
    }
}

