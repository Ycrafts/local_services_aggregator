<?php

namespace App\Services;

use App\Models\RequestedJob;
use Illuminate\Support\Facades\Log;

class RequestedJobStatusService
{
    // Define valid statuses
    const STATUS_PENDING = 'pending';
    const STATUS_INTERESTED = 'interested';
    const STATUS_SELECTED = 'selected';
    const STATUS_OFFER_ACCEPTED = 'offer_accepted';
    const STATUS_DECLINED = 'declined';

    // Define allowed transitions
    private static $allowedTransitions = [
        self::STATUS_PENDING => [self::STATUS_INTERESTED, self::STATUS_DECLINED],
        self::STATUS_INTERESTED => [self::STATUS_SELECTED, self::STATUS_DECLINED],
        self::STATUS_SELECTED => [self::STATUS_OFFER_ACCEPTED, self::STATUS_DECLINED],
        self::STATUS_OFFER_ACCEPTED => [],
        self::STATUS_DECLINED => [],
    ];

    /**
     * Transition a requested job to a new status.
     *
     * @param RequestedJob $requestedJob
     * @param string $newStatus
     * @return bool
     */
    public static function transition(RequestedJob $requestedJob, string $newStatus): bool
    {
        if (!in_array($newStatus, self::$allowedTransitions[$requestedJob->status] ?? [])) {
            Log::warning("Invalid status transition: {$requestedJob->status} -> {$newStatus}");
            return false;
        }

        $requestedJob->status = $newStatus;
        $requestedJob->save();

        // Here you can add logging or trigger events if needed
        Log::info("RequestedJob {$requestedJob->id} status changed to {$newStatus}");

        return true;
    }
} 