<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteEnrollmentEventRequest;
use App\Services\EnrollmentEvents\EnrollmentEventConsumer;
use App\Services\EnrollmentEvents\EnrollmentEventFilter;
use App\Services\EnrollmentEvents\EnrollmentEventRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnrollmentEventController extends Controller
{
    public function __invoke(
        RouteEnrollmentEventRequest $request,
        EnrollmentEventRouter $router,
        EnrollmentEventFilter $filter,
        EnrollmentEventConsumer $consumer,
    ): JsonResponse {
        $message = $request->validated();
        $message['correlation_id'] = $request->header('X-Correlation-ID') ?: (string) Str::uuid();

        if (! $filter->allows($message)) {
            return response()->json([
                'event_id' => $message['id'],
                'type' => $message['type'],
                'destination' => 'filtrados',
                'status' => 'filtrado',
                'reason' => 'La prioridad baja no supera el filtro de admisión.',
                'correlation_id' => $message['correlation_id'],
            ], 200)->header('X-Correlation-ID', $message['correlation_id']);
        }

        $destination = $router->destinationFor($message['type']);
        Log::info('message_router.decided', [
            'event_id' => $message['id'],
            'correlation_id' => $message['correlation_id'],
            'type' => $message['type'],
            'destination' => $destination,
        ]);

        if ($destination === 'no_enrutables') {
            Log::warning('message_router.unroutable', [
                'event_id' => $message['id'],
                'correlation_id' => $message['correlation_id'],
                'type' => $message['type'],
                'destination' => $destination,
            ]);

            return response()->json([
                'event_id' => $message['id'],
                'type' => $message['type'],
                'destination' => $destination,
                'status' => 'no_enrutable',
                'reason' => 'No existe una regla para este tipo de evento.',
                'correlation_id' => $message['correlation_id'],
            ], 202)->header('X-Correlation-ID', $message['correlation_id']);
        }

        $effect = $consumer->consume(
            $destination,
            (string) $request->user()->getAuthIdentifier(),
            $message,
        );

        return response()->json([
            'destination' => $destination,
            'status' => 'consumido',
            'event_id' => $message['id'],
            'correlation_id' => $message['correlation_id'],
            'effect' => $effect,
        ], 200)->header('X-Correlation-ID', $message['correlation_id']);
    }
}
