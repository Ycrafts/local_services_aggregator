<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\RequestedJob;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Log;

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

    public function expressInterest(Request $request, $jobId)
    {
        $providerProfile = $request->user()->providerProfile;

        if (!$providerProfile) {
            return response()->json(['message' => 'Provider profile not found.'], 404);
        }

        $requestedJob = RequestedJob::where('job_id', $jobId)
            ->where('provider_profile_id', $providerProfile->id)
            ->first();

        if (!$requestedJob) {
            return response()->json(['message' => 'Job not found or not assigned to this provider.'], 404);
        }

        $requestedJob->update(['is_interested' => true]);

        return response()->json(['message' => 'Interest expressed successfully.']);
    }

    public function interestedProviders($jobId)
    {
        $job = Job::findOrFail($jobId);

        // Optionally: check that the user owns the job
        // if (auth()->id() !== $job->user_id) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $interestedProviders = RequestedJob::with('providerProfile.user') // eager load provider and user
            ->where('job_id', $job->id)
            ->where('is_interested', true)
            ->get();

        return response()->json($interestedProviders);
    }

    public function selectProvider(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($request->user()->id !== $job->user_id) {
            return response()->json(['message' => 'Unauthorized: You are not the job owner.'], 403);
        }

        $validated = $request->validate([
            'provider_profile_id' => [
                'required',
                'exists:provider_profiles,id',
                Rule::exists('requested_jobs', 'provider_profile_id')
                    ->where('job_id', $job->id)
                    ->where('is_interested', true),
            ],
        ]);

        $providerProfileId = $validated['provider_profile_id'];

        // Confirm profile exists (debugging help)
        $profile = ProviderProfile::find($providerProfileId);
        if (!$profile) {
            Log::warning("ProviderProfile not found with ID {$providerProfileId}");
            return response()->json(['message' => 'Provider profile not found in DB.'], 404);
        }

        $job->assigned_provider_id = $providerProfileId;
        $job->status = 'assigned';
        $job->save();

        RequestedJob::where('job_id', $job->id)
            ->where('provider_profile_id', $providerProfileId)
            ->update(['is_selected' => true]);

        return response()->json([
            'message' => 'Provider selected successfully.',
            'job' => $job->load('assignedProvider.user'),
        ]);
    }

    public function providerRequestedJobs(Request $request){
        $providerProfile = ProviderProfile::where('user_id', Auth::id())->first();
        if (!$providerProfile) {
            // Handle the case where the user has no provider profile
            return response()->json(['message' => 'Provider profile not found.'], 404);
        }

        $jobs = RequestedJob::where('provider_profile_id', $providerProfile->id)->get();

        return response()->json($jobs);
    }
}

