<?php

namespace App\Services\EnrollmentEvents;

use Illuminate\Support\Facades\Log;

class EnrollmentEventConsumer
{
    /**
     * @param array<string, mixed> $message
     * @return array{destination: string, status: string, event_id: string, correlation_id: string}
     */
    public function consume(string $destination, array $message): array
    {
        $result = [
            'destination' => $destination,
            'status' => 'consumido',
            'event_id' => $message['id'],
            'correlation_id' => $message['correlation_id'],
        ];

        Log::info('message_consumer.processed', $result + ['type' => $message['type']]);

        return $result;
    }
}
