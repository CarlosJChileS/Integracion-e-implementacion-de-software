<?php

namespace App\Services\EnrollmentEvents;

use Illuminate\Support\Facades\Log;

class EnrollmentEventFilter
{
    /** @param array<string, mixed> $message */
    public function allows(string $destination, array $message): bool
    {
        if ($destination !== 'inscripciones' || $message['type'] !== 'enrollment.created') {
            return true;
        }

        $allowed = in_array($message['priority'], ['normal', 'alta'], true);

        if (! $allowed) {
            Log::notice('message_filter.rejected', [
                'event_id' => $message['id'],
                'correlation_id' => $message['correlation_id'],
                'destination' => $destination,
                'reason' => 'La prioridad baja no activa el consumidor de inscripciones.',
            ]);
        }

        return $allowed;
    }
}
