<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class JobStatusService
{
    // Define valid statuses
    const STATUS_OPEN = 'open';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Define allowed transitions
    private static $allowedTransitions = [
        self::STATUS_OPEN => [self::STATUS_ASSIGNED, self::STATUS_CANCELLED],
        self::STATUS_ASSIGNED => [self::STATUS_IN_PROGRESS, self::STATUS_CANCELLED],
        self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    /**
     * Transition a job to a new status.
     *
     * @param Job $job
     * @param string $newStatus
     * @return bool
     */
    public static function transition(Job $job, string $newStatus): bool
    {
        if (!in_array($newStatus, self::$allowedTransitions[$job->status] ?? [])) {
            Log::warning("Invalid status transition: {$job->status} -> {$newStatus}");
            return false;
        }

        $job->status = $newStatus;
        $job->save();

        // Here you can add logging or trigger events if needed
        Log::info("Job {$job->id} status changed to {$newStatus}");

        // Notify assigned provider if status changes and provider is assigned
        if ($job->assigned_provider_id && in_array($newStatus, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            $provider = $job->assignedProvider;
            if ($provider && $provider->user_id) {
                Notification::create([
                    'user_id' => $provider->user_id,
                    'job_id' => $job->id,
                    'type' => 'status_change',
                    'message' => "The job status has changed to {$newStatus}.",
                ]);
            }
        }

        // Notify the customer if job status changes
        if (in_array($newStatus, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            Notification::create([
                'user_id' => $job->user_id,
                'job_id' => $job->id,
                'type' => 'status_change',
                'message' => "The job status has changed to {$newStatus}.",
            ]);
        }

        return true;
    }
} 