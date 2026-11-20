<?php

namespace App\Services\EnrollmentEvents;

use App\Services\EnrollmentService;
use Illuminate\Support\Facades\Log;

class EnrollmentCancelledConsumer
{
    /** @param array<string, mixed> $message
     * @return array{action: string, enrollment_id: string}
     */
    public function consume(string $userId, array $message, EnrollmentService $service): array
    {
        $enrollmentId = $message['data']['enrollment_id'];
        $service->deleteForUser($userId, $enrollmentId);
        $effect = ['action' => 'cancelled', 'enrollment_id' => $enrollmentId];

        Log::info('message_consumer.processed', $effect + [
            'consumer' => 'cancelaciones',
            'event_id' => $message['id'],
            'correlation_id' => $message['correlation_id'],
        ]);

        return $effect;
    }
}
