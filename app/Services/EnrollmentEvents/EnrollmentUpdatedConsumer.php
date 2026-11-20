<?php

namespace App\Services\EnrollmentEvents;

use App\Data\Enrollment\UpdateEnrollmentData;
use App\Services\EnrollmentService;
use Illuminate\Support\Facades\Log;

class EnrollmentUpdatedConsumer
{
    /** @param array<string, mixed> $message
     * @return array{action: string, enrollment_id: string, course_slug: string}
     */
    public function consume(string $userId, array $message, EnrollmentService $service): array
    {
        $enrollment = $service->updateForUser(
            $userId,
            $message['data']['enrollment_id'],
            new UpdateEnrollmentData(
                $message['data']['course_slug'],
                $message['data']['cohort_code'] ?? null,
                array_key_exists('cohort_code', $message['data']),
            ),
        );
        $effect = [
            'action' => 'updated',
            'enrollment_id' => $enrollment->id,
            'course_slug' => $message['data']['course_slug'],
        ];

        Log::info('message_consumer.processed', $effect + [
            'consumer' => 'actualizaciones',
            'event_id' => $message['id'],
            'correlation_id' => $message['correlation_id'],
        ]);

        return $effect;
    }
}
