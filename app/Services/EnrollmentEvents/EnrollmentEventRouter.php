<?php

namespace App\Services\EnrollmentEvents;

class EnrollmentEventRouter
{
    /** @var array<string, string> */
    private const DESTINATIONS = [
        'enrollment.created' => 'inscripciones',
        'enrollment.updated' => 'actualizaciones',
        'enrollment.cancelled' => 'cancelaciones',
    ];

    public function destinationFor(string $type): string
    {
        return self::DESTINATIONS[$type] ?? 'no_enrutables';
    }
}
