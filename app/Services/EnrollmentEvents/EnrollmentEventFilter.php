<?php

namespace App\Services\EnrollmentEvents;

use Illuminate\Support\Facades\Log;

class EnrollmentEventFilter
{
    /** @param array<string, mixed> $message */
    public function allows(array $message): bool
    {
        $allowed = $message['priority'] !== 'baja';

        if (! $allowed) {
            Log::notice('message_filter.rejected', [
                'event_id' => $message['id'],
                'correlation_id' => $message['correlation_id'],
                'type' => $message['type'],
                'reason' => 'La prioridad baja no supera el filtro de admisión.',
            ]);
        }

        return $allowed;
    }
}
