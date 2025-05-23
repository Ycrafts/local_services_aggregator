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
use App\Services\JobStatusService;
use App\Services\RequestedJobStatusService;
use App\Models\Notification;


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
            'estimated_cost' => $validated['proposed_price'],
            'status'         => 'open',
        ]);

        $matchedProviders = ProviderProfile::whereHas('jobTypes', function ($query) use ($job) {
            $query->where('job_type_id', $job->job_type_id);
        })->get();
    
        foreach ($matchedProviders as $provider) {
            RequestedJob::create([
                'job_id' => $job->id,
                'provider_profile_id' => $provider->id,
                'status' => 'pending'
            ]);
            Notification::create([
                'user_id' => $provider->user_id,
                'job_id' => $job->id,
                'type' => 'new_job',
                'message' => 'A new job matching your skills has been posted.',
            ]);
        }
    
        return response()->json([
            'message' => 'Job posted successfully.',
            'job'     => $job
        ], 201);

    }

    public function index()
    {
        $jobs = Job::with('jobType')->where('user_id', Auth::id())->paginate(10);  // 10 jobs per page
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

        if (!RequestedJobStatusService::transition($requestedJob, RequestedJobStatusService::STATUS_INTERESTED)) {
            return response()->json(['message' => 'Invalid status transition.'], 400);
        }

        $requestedJob->is_interested = true;
        $requestedJob->save();

        $job = $requestedJob->job;
        if ($job && $job->user_id) {
            Notification::create([
                'user_id' => $job->user_id,
                'job_id' => $job->id,
                'type' => 'provider_interested',
                'message' => 'A provider has expressed interest in your job.',
            ]);
        }

        return response()->json(['message' => 'Interest expressed successfully.']);
    }

    public function interestedProviders($jobId)
    {
        $job = Job::findOrFail($jobId);

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

        $profile = ProviderProfile::find($providerProfileId);
        if (!$profile) {
            Log::warning("ProviderProfile not found with ID {$providerProfileId}");
            return response()->json(['message' => 'Provider profile not found in DB.'], 404);
        }

        $job->assigned_provider_id = $providerProfileId;
        $job->status = 'in_progress';
        $job->save();

        RequestedJob::where('job_id', $job->id)
            ->where('provider_profile_id', $providerProfileId)
            ->update(['status' => RequestedJobStatusService::STATUS_SELECTED]);

        Notification::create([
            'user_id' => $profile->user_id,
            'job_id' => $job->id,
            'type' => 'job_selected',
            'message' => 'You have been selected for a job!',
        ]);

        Notification::create([
            'user_id' => $job->user_id,
            'job_id' => $job->id,
            'type' => 'provider_assigned',
            'message' => 'A provider has been assigned to your job.',
        ]);

        return response()->json([
            'message' => 'Provider selected successfully.',
            'job' => $job->load('assignedProvider.user'),
        ]);
    }
    public function providerRequestedJobs(Request $request)
    {
        $providerProfile = ProviderProfile::where('user_id', Auth::id())->first();

        if (!$providerProfile) {
            return response()->json(['message' => 'Provider profile not found.'], 404);
        }

        $jobs = RequestedJob::where('provider_profile_id', $providerProfile->id)
            ->whereHas('job', function ($query) {
                $query->where('status', 'open');
            })
            ->with('job') 
            ->get();

        return response()->json($jobs);
    }

    public function rateProvider(Request $request, $jobId)
    {
        $user = $request->user();
        $job = Job::findOrFail($jobId);

        if ($user->id !== $job->user_id || $user->role !== 'customer') {
            return response()->json(['message' => 'Only the customer who owns this job can rate the provider.'], 403);
        }

        if ($job->status !== 'completed') {
            return response()->json(['message' => 'You can only rate after the job is completed.'], 400);
        }

        if (!$job->assigned_provider_id) {
            return response()->json(['message' => 'No provider assigned to this job.'], 400);
        }

        $existing = \App\Models\Rating::where('job_id', $job->id)
            ->where('customer_id', $user->id)
            ->first();
        if ($existing) {
            return response()->json(['message' => 'You have already rated this provider for this job.'], 400);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $providerProfileId = $job->assigned_provider_id;

        $rating = \App\Models\Rating::create([
            'job_id' => $job->id,
            'provider_profile_id' => $providerProfileId,
            'customer_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $provider = \App\Models\ProviderProfile::find($providerProfileId);
        $avg = \App\Models\Rating::where('provider_profile_id', $providerProfileId)->avg('rating');
        $provider->rating = $avg;
        $provider->save();

        return response()->json([
            'message' => 'Rating submitted successfully.',
            'rating' => $rating
        ], 201);
    }

    public function providerMarkDone(Request $request, $jobId)
    {
        $user = $request->user();
        $job = Job::findOrFail($jobId);

        if ($user->role !== 'provider' || !$job->assigned_provider_id || $job->assigned_provider_id != $user->providerProfile->id) {
            return response()->json(['message' => 'Only the assigned provider can mark this job as done.'], 403);
        }

        if (!in_array($job->status, ['assigned', 'in_progress'])) {
            return response()->json(['message' => 'Job is not in a state that can be marked as done.'], 400);
        }

        $job->provider_marked_done_at = now();
        $job->status = 'in_progress'; 
        $job->save();

        Notification::create([
            'user_id' => $job->user_id,
            'job_id' => $job->id,
            'type' => 'status_change',
            'message' => 'Provider has marked the job as done. Please confirm completion.',
        ]);

        return response()->json(['message' => 'Job marked as done. Awaiting customer confirmation.']);
    }

    public function customerConfirmComplete(Request $request, $jobId)
    {
        $user = $request->user();
        $job = Job::findOrFail($jobId);

        if ($user->role !== 'customer' || $user->id != $job->user_id) {
            return response()->json(['message' => 'Only the customer can confirm completion.'], 403);
        }

        if (!$job->provider_marked_done_at) {
            return response()->json(['message' => 'Provider has not marked this job as done yet.'], 400);
        }

        //  already completed
        if ($job->status === 'completed') {
            return response()->json(['message' => 'Job is already completed.'], 400);
        }

        // Use JobStatusService to transition to completed
        if (!JobStatusService::transition($job, JobStatusService::STATUS_COMPLETED)) {
            return response()->json(['message' => 'Invalid status transition.'], 400);
        }

        return response()->json(['message' => 'Job marked as completed.']);
    }

}

