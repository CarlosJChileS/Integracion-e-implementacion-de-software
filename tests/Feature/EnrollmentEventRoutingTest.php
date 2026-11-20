<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnrollmentEventRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_event_reaches_enrollment_consumer_and_propagates_correlation_id(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $message = $this->message('enrollment.created');
        $response = $this->withHeader('X-Correlation-ID', 'practica-32-creado')
            ->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertHeader('X-Correlation-ID', 'practica-32-creado')
            ->assertJsonPath('destination', 'inscripciones')
            ->assertJsonPath('status', 'consumido')
            ->assertJsonPath('correlation_id', 'practica-32-creado');

        $this->saveEvidence('01-creada', $message, $response->status(), $response->json());
    }

    public function test_updated_event_reaches_update_consumer(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $message = $this->message('enrollment.updated');
        $response = $this->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertJsonPath('destination', 'actualizaciones')
            ->assertJsonPath('status', 'consumido');

        $this->saveEvidence('02-actualizada', $message, $response->status(), $response->json());
    }

    public function test_cancelled_event_reaches_cancellation_consumer(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $message = $this->message('enrollment.cancelled');
        $response = $this->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertJsonPath('destination', 'cancelaciones')
            ->assertJsonPath('status', 'consumido');

        $this->saveEvidence('03-cancelada', $message, $response->status(), $response->json());
    }

    public function test_unknown_type_is_recorded_as_unroutable(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Log::spy();
        $message = $this->message('enrollment.archived');
        $response = $this->postJson('/api/enrollment-events', $message)
            ->assertStatus(202)
            ->assertJsonPath('destination', 'no_enrutables')
            ->assertJsonPath('status', 'no_enrutable');

        Log::shouldHaveReceived('warning')->once()->with('message_router.unroutable', \Mockery::type('array'));
        $this->saveEvidence('04-desconocido', $message, $response->status(), $response->json());
    }

    public function test_low_priority_created_event_is_filtered_before_the_consumer(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Log::spy();
        $message = $this->message('enrollment.created');
        $message['priority'] = 'baja';
        $response = $this->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertJsonPath('destination', 'inscripciones')
            ->assertJsonPath('status', 'filtrado');

        Log::shouldHaveReceived('notice')->once()->with('message_filter.rejected', \Mockery::type('array'));
        $this->saveEvidence('05-filtrado', $message, $response->status(), $response->json());
    }

    public function test_missing_required_field_is_rejected_before_routing(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $message = $this->message('enrollment.created');
        unset($message['data']['course_slug']);
        $response = $this->postJson('/api/enrollment-events', $message)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data.course_slug');

        $this->saveEvidence('06-validacion', $message, $response->status(), $response->json());
    }

    /** @return array<string, mixed> */
    private function message(string $type): array
    {
        return [
            'id' => '11111111-1111-4111-8111-111111111111',
            'type' => $type,
            'priority' => 'normal',
            'data' => [
                'enrollment_id' => '22222222-2222-4222-8222-222222222222',
                'course_slug' => 'prompting-para-docentes',
            ],
        ];
    }

    /** @param array<string, mixed> $message
     *  @param array<string, mixed> $response
     */
    private function saveEvidence(string $case, array $message, int $status, array $response): void
    {
        file_put_contents(base_path('3.2/evidencias/'.$case.'.json'), json_encode([
            'caso' => $case,
            'solicitud' => $message,
            'status_http' => $status,
            'respuesta' => $response,
            'verificacion' => 'OK',
            'ejecucion' => 'Cliente HTTP de pruebas de Laravel: middleware, validación, controlador y servicios reales.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
