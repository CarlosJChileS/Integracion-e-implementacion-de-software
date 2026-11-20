<?php

namespace App\Services\EnrollmentEvents;

use App\Services\EnrollmentService;

class EnrollmentEventConsumer
{
    public function __construct(
        private readonly EnrollmentCreatedConsumer $created,
        private readonly EnrollmentUpdatedConsumer $updated,
        private readonly EnrollmentCancelledConsumer $cancelled,
        private readonly EnrollmentService $enrollments,
    ) {}

    /** @param array<string, mixed> $message
     * @return array{action: string, enrollment_id: string, course_slug?: string}
     */
    public function consume(string $destination, string $userId, array $message): array
    {
        return match ($destination) {
            'inscripciones' => $this->created->consume($userId, $message, $this->enrollments),
            'actualizaciones' => $this->updated->consume($userId, $message, $this->enrollments),
            'cancelaciones' => $this->cancelled->consume($userId, $message, $this->enrollments),
        };
    }
}
