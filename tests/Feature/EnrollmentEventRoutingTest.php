<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
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
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Log::spy();
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        $message = $this->message('enrollment.created');
        unset($message['data']['enrollment_id']);
        $response = $this->withHeader('X-Correlation-ID', 'practica-32-creado')
            ->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertHeader('X-Correlation-ID', 'practica-32-creado')
            ->assertJsonPath('destination', 'inscripciones')
            ->assertJsonPath('status', 'consumido')
            ->assertJsonPath('correlation_id', 'practica-32-creado')
            ->assertJsonPath('effect.action', 'created');

        $this->assertDatabaseHas('enrollments', [
            'id' => $response->json('effect.enrollment_id'),
            'user_id' => $user->id,
        ]);
        Log::shouldHaveReceived('info')->withArgs(
            fn (string $event, array $context): bool => $event === 'message_consumer.processed'
                && $context['consumer'] === 'inscripciones'
                && $context['action'] === 'created'
                && $context['correlation_id'] === 'practica-32-creado',
        )->once();

        $this->saveEvidence('01-creada', $message, $response->status(), $response->json(), [
            'event' => 'message_consumer.processed', 'action' => 'created', 'consumer' => 'inscripciones',
            'effect' => 'Inscripción persistida en SQLite con el usuario autenticado.',
        ]);
    }

    public function test_updated_event_reaches_update_consumer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Log::spy();
        $courseA = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        $courseB = Course::create(['slug' => 'curso-b', 'name' => 'Curso B']);
        $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $courseA->id]);
        $message = $this->message('enrollment.updated');
        $message['data']['enrollment_id'] = $enrollment->id;
        $message['data']['course_slug'] = 'curso-b';
        $response = $this->withHeader('X-Correlation-ID', 'practica-32-actualizada')
            ->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertJsonPath('destination', 'actualizaciones')
            ->assertJsonPath('status', 'consumido')
            ->assertJsonPath('effect.action', 'updated')
            ->assertJsonPath('effect.enrollment_id', $enrollment->id);

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'course_id' => $courseB->id]);
        Log::shouldHaveReceived('info')->withArgs(
            fn (string $event, array $context): bool => $event === 'message_consumer.processed'
                && $context['consumer'] === 'actualizaciones'
                && $context['action'] === 'updated'
                && $context['enrollment_id'] === $enrollment->id,
        )->once();
        $this->saveEvidence('02-actualizada', $message, $response->status(), $response->json(), [
            'event' => 'message_consumer.processed', 'action' => 'updated', 'consumer' => 'actualizaciones',
            'effect' => 'Inscripción existente actualizada mediante EnrollmentService.',
        ]);
    }

    public function test_cancelled_event_reaches_cancellation_consumer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Log::spy();
        $course = Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
        $message = $this->message('enrollment.cancelled');
        $message['data']['enrollment_id'] = $enrollment->id;
        $response = $this->withHeader('X-Correlation-ID', 'practica-32-cancelada')
            ->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertJsonPath('destination', 'cancelaciones')
            ->assertJsonPath('status', 'consumido')
            ->assertJsonPath('effect.action', 'cancelled')
            ->assertJsonPath('effect.enrollment_id', $enrollment->id);

        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
        Log::shouldHaveReceived('info')->withArgs(
            fn (string $event, array $context): bool => $event === 'message_consumer.processed'
                && $context['consumer'] === 'cancelaciones'
                && $context['action'] === 'cancelled'
                && $context['enrollment_id'] === $enrollment->id,
        )->once();
        $this->saveEvidence('03-cancelada', $message, $response->status(), $response->json(), [
            'event' => 'message_consumer.processed', 'action' => 'cancelled', 'consumer' => 'cancelaciones',
            'effect' => 'Inscripción eliminada mediante EnrollmentService.',
        ]);
    }

    public function test_unknown_type_is_recorded_as_unroutable(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Log::spy();
        $message = $this->message('enrollment.archived');
        $response = $this->withHeader('X-Correlation-ID', 'practica-32-desconocido')
            ->postJson('/api/enrollment-events', $message)
            ->assertStatus(202)
            ->assertJsonPath('destination', 'no_enrutables')
            ->assertJsonPath('status', 'no_enrutable');

        Log::shouldHaveReceived('warning')->once()->with('message_router.unroutable', \Mockery::type('array'));
        $this->saveEvidence('04-desconocido', $message, $response->status(), $response->json(), [
            'event' => 'message_router.unroutable', 'destination' => 'no_enrutables',
            'effect' => 'No se llamó ningún consumidor; evento registrado para revisión.',
        ]);
    }

    public function test_low_priority_created_event_is_filtered_before_the_consumer(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Log::spy();
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        $message = $this->message('enrollment.created');
        $message['priority'] = 'baja';
        unset($message['data']['enrollment_id']);
        $response = $this->withHeader('X-Correlation-ID', 'practica-32-filtrado')
            ->postJson('/api/enrollment-events', $message)
            ->assertOk()
            ->assertJsonPath('destination', 'filtrados')
            ->assertJsonPath('status', 'filtrado');

        Log::shouldHaveReceived('notice')->once()->with('message_filter.rejected', \Mockery::type('array'));
        Log::shouldNotHaveReceived('info');
        $this->assertDatabaseCount('enrollments', 0);
        $this->saveEvidence('05-filtrado', $message, $response->status(), $response->json(), [
            'event' => 'message_filter.rejected', 'destination' => 'filtrados',
            'effect' => 'El filtro rechazó el mensaje; no se creó ninguna inscripción.',
        ]);
    }

    public function test_missing_required_field_is_rejected_before_routing(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Log::spy();
        $message = $this->message('enrollment.created');
        unset($message['data']['course_slug']);
        $response = $this->withHeader('X-Correlation-ID', 'practica-32-validacion')
            ->postJson('/api/enrollment-events', $message)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data.course_slug');
        Log::shouldNotHaveReceived('info');

        $this->saveEvidence('06-validacion', $message, $response->status(), $response->json(), [
            'event' => 'validation.rejected', 'effect' => 'El enrutador y los consumidores no se ejecutaron.',
        ]);
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
     * @param  array<string, mixed>  $response
     */
    private function saveEvidence(string $case, array $message, int $status, array $response, array $trace): void
    {
        file_put_contents(base_path('3.2/evidencias/'.$case.'.json'), json_encode([
            'caso' => $case,
            'solicitud' => $message,
            'status_http' => $status,
            'respuesta' => $response,
            'traza_verificada' => $trace,
            'correlation_id' => $response['correlation_id'] ?? null,
            'evidencia_consumidor' => $response['effect'] ?? null,
            'verificacion' => 'OK',
            'ejecucion' => 'Cliente HTTP de pruebas de Laravel con Sanctum, base SQLite temporal y EnrollmentService real.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
