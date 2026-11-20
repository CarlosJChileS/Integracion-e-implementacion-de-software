<?php

namespace App\Services\EnrollmentEvents;

use App\Data\Enrollment\EnrollCourseData;
use App\Services\EnrollmentService;
use Illuminate\Support\Facades\Log;

class EnrollmentCreatedConsumer
{
    /** @param array<string, mixed> $message
     * @return array{action: string, enrollment_id: string, course_slug: string}
     */
    public function consume(string $userId, array $message, EnrollmentService $service): array
    {
        $enrollment = $service->createForUser($userId, new EnrollCourseData(
            $message['data']['course_slug'],
            $message['data']['cohort_code'] ?? null,
        ));
        $effect = [
            'action' => 'created',
            'enrollment_id' => $enrollment->id,
            'course_slug' => $message['data']['course_slug'],
        ];

        Log::info('message_consumer.processed', $effect + [
            'consumer' => 'inscripciones',
            'event_id' => $message['id'],
            'correlation_id' => $message['correlation_id'],
        ]);

        return $effect;
    }
}
