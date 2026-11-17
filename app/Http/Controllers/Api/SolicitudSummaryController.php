<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SolicitudSummaryController
{
    private const USER_ID = 7;

    public function __invoke(Request $request, string $solicitudId): JsonResponse
    {
        $solicitudId = (int) $solicitudId;
        $correlationId = $this->correlationId($request);
        $headers = ['X-Correlation-ID' => $correlationId];

        $currentService = 'usuarios';

        try {
            $userResponse = Http::timeout(3)
                ->withHeaders($headers)
                ->get(rtrim(config('services.practica33.users_url'), '/').'/usuarios/'.self::USER_ID);

            if (! $userResponse->successful()) {
                return $this->upstreamFailure('usuarios', $correlationId, $userResponse->status());
            }

            $user = $userResponse->json();
            if (! $this->validUser($user)) {
                return $this->upstreamFailure('usuarios', $correlationId, $userResponse->status(), 'invalid_response');
            }

            $currentService = 'estados';
            $stateResponse = Http::timeout(3)
                ->withHeaders($headers)
                ->get(rtrim(config('services.practica33.states_url'), '/').'/estados/'.$solicitudId);

            if (! $stateResponse->successful()) {
                return $this->upstreamFailure('estados', $correlationId, $stateResponse->status());
            }

            $state = $stateResponse->json();
            if (! $this->validState($state, $solicitudId)) {
                return $this->upstreamFailure('estados', $correlationId, $stateResponse->status(), 'invalid_response');
            }
        } catch (ConnectionException) {
            return $this->upstreamFailure($currentService, $correlationId, null, 'connection_error');
        }

        return response()->json([
            'solicitud_id' => $solicitudId,
            'usuario' => $user,
            'estado' => $state['estado'],
            'correlation_id' => $correlationId,
        ])->header('X-Correlation-ID', $correlationId);
    }

    private function correlationId(Request $request): string
    {
        $incoming = $request->header('X-Correlation-ID');

        if (is_string($incoming) && preg_match('/\A[A-Za-z0-9._:-]{1,64}\z/', $incoming) === 1) {
            return $incoming;
        }

        return (string) Str::uuid();
    }

    private function validUser(mixed $user): bool
    {
        return is_array($user)
            && ($user['id'] ?? null) === self::USER_ID
            && is_string($user['nombre'] ?? null)
            && $user['nombre'] !== '';
    }

    private function validState(mixed $state, int $solicitudId): bool
    {
        return is_array($state)
            && ($state['solicitud_id'] ?? null) === $solicitudId
            && is_string($state['estado'] ?? null)
            && $state['estado'] !== '';
    }

    private function upstreamFailure(string $service, string $correlationId, ?int $status, string $reason = 'upstream_error'): JsonResponse
    {
        Log::warning('No se pudo consultar un servicio del resumen de solicitud.', [
            'service' => $service,
            'correlation_id' => $correlationId,
            'status' => $status,
            'reason' => $reason,
        ]);

        return response()->json([
            'mensaje' => 'No fue posible completar el resumen de la solicitud.',
            'correlation_id' => $correlationId,
        ], 502)->header('X-Correlation-ID', $correlationId);
    }
}
