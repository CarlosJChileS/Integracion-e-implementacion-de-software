<?php

namespace Tests\Feature\Practica33\Orchestration;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class SolicitudSummaryTest extends TestCase
{
    private const USERS_URL = 'https://users.test';

    private const STATES_URL = 'https://states.test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.practica33.users_url' => self::USERS_URL,
            'services.practica33.states_url' => self::STATES_URL,
        ]);
    }

    public function test_it_combines_both_services_and_propagates_the_correlation_id(): void
    {
        Http::fake([
            self::USERS_URL.'/usuarios/7' => Http::response(['id' => 7, 'nombre' => 'Ana']),
            self::STATES_URL.'/estados/101' => Http::response(['solicitud_id' => 101, 'estado' => 'pendiente']),
        ]);

        $response = $this->withHeader('X-Correlation-ID', 'practica33-demo-01')
            ->getJson('/api/resumen-solicitud/101');

        $response->assertOk()->assertExactJson([
            'solicitud_id' => 101,
            'usuario' => ['id' => 7, 'nombre' => 'Ana'],
            'estado' => 'pendiente',
            'correlation_id' => 'practica33-demo-01',
        ])->assertHeader('X-Correlation-ID', 'practica33-demo-01');

        Http::assertSent(fn (Request $request) => $request->url() === self::USERS_URL.'/usuarios/7'
            && $request->method() === 'GET'
            && $request->hasHeader('X-Correlation-ID', 'practica33-demo-01'));
        Http::assertSent(fn (Request $request) => $request->url() === self::STATES_URL.'/estados/101'
            && $request->method() === 'GET'
            && $request->hasHeader('X-Correlation-ID', 'practica33-demo-01'));
        Http::assertSentCount(2);
    }

    public function test_it_generates_a_correlation_id_when_the_incoming_value_is_unsafe(): void
    {
        Http::fake([
            self::USERS_URL.'/usuarios/7' => Http::response(['id' => 7, 'nombre' => 'Ana']),
            self::STATES_URL.'/estados/101' => Http::response(['solicitud_id' => 101, 'estado' => 'pendiente']),
        ]);

        $response = $this->withHeader('X-Correlation-ID', "invalid\r\nvalue")
            ->getJson('/api/resumen-solicitud/101');

        $response->assertOk();
        $correlationId = $response->json('correlation_id');
        $this->assertTrue(Str::isUuid($correlationId));
        $response->assertHeader('X-Correlation-ID', $correlationId);
        Http::assertSent(fn (Request $request) => $request->hasHeader('X-Correlation-ID', $correlationId));
    }

    public function test_it_returns_controlled_502_and_logs_when_a_service_returns_an_error(): void
    {
        Http::fake([
            self::USERS_URL.'/usuarios/7' => Http::response(['message' => 'private upstream detail'], 503),
            self::STATES_URL.'/estados/101' => Http::response(['solicitud_id' => 101, 'estado' => 'pendiente']),
        ]);
        Log::shouldReceive('warning')->once()->with(
            'No se pudo consultar un servicio del resumen de solicitud.',
            \Mockery::on(fn (array $context) => $context['service'] === 'usuarios'
                && $context['correlation_id'] === 'practica33-failure-01'
                && $context['status'] === 503)
        );

        $response = $this->withHeader('X-Correlation-ID', 'practica33-failure-01')
            ->getJson('/api/resumen-solicitud/101');

        $response->assertStatus(502)->assertExactJson([
            'mensaje' => 'No fue posible completar el resumen de la solicitud.',
            'correlation_id' => 'practica33-failure-01',
        ])->assertHeader('X-Correlation-ID', 'practica33-failure-01');
        $this->assertStringNotContainsString('private upstream detail', $response->getContent());
        Http::assertSentCount(1);
    }

    public function test_it_returns_controlled_502_when_a_service_cannot_be_reached(): void
    {
        Log::shouldReceive('warning')->once()->with(
            'No se pudo consultar un servicio del resumen de solicitud.',
            \Mockery::on(fn (array $context) => $context['service'] === 'estados'
                && $context['correlation_id'] !== ''
                && $context['reason'] === 'connection_error')
        );

        Http::fake([
            self::USERS_URL.'/usuarios/7' => Http::response(['id' => 7, 'nombre' => 'Ana']),
            self::STATES_URL.'/estados/101' => fn () => throw new ConnectionException('private socket trace'),
        ]);

        $response = $this->getJson('/api/resumen-solicitud/101');

        $response->assertStatus(502)->assertJson([
            'mensaje' => 'No fue posible completar el resumen de la solicitud.',
        ]);
        $this->assertStringNotContainsString('private socket trace', $response->getContent());
        $this->assertNotNull($response->json('correlation_id'));
    }

    public function test_it_identifies_a_connection_failure_from_the_users_service(): void
    {
        Http::fake([
            self::USERS_URL.'/usuarios/7' => fn () => throw new ConnectionException('private users socket trace'),
            self::STATES_URL.'/estados/101' => Http::response(['solicitud_id' => 101, 'estado' => 'pendiente']),
        ]);
        Log::shouldReceive('warning')->once()->with(
            'No se pudo consultar un servicio del resumen de solicitud.',
            \Mockery::on(fn (array $context) => $context['service'] === 'usuarios'
                && $context['correlation_id'] !== ''
                && $context['reason'] === 'connection_error')
        );

        $response = $this->getJson('/api/resumen-solicitud/101');

        $response->assertStatus(502)->assertJson([
            'mensaje' => 'No fue posible completar el resumen de la solicitud.',
        ]);
        $this->assertStringNotContainsString('private users socket trace', $response->getContent());
        Http::assertNotSent(fn (Request $request) => $request->url() === self::STATES_URL.'/estados/101');
    }

    public function test_it_returns_controlled_502_when_the_state_service_status_is_not_successful(): void
    {
        Http::fake([
            self::USERS_URL.'/usuarios/7' => Http::response(['id' => 7, 'nombre' => 'Ana']),
            self::STATES_URL.'/estados/101' => Http::response(['mensaje' => 'private state detail'], 503),
        ]);
        Log::shouldReceive('warning')->once()->with(
            'No se pudo consultar un servicio del resumen de solicitud.',
            \Mockery::on(fn (array $context) => $context['service'] === 'estados'
                && $context['status'] === 503
                && $context['reason'] === 'upstream_error')
        );

        $response = $this->withHeader('X-Correlation-ID', 'practica33-states-503')
            ->getJson('/api/resumen-solicitud/101');

        $response->assertStatus(502)->assertJson([
            'mensaje' => 'No fue posible completar el resumen de la solicitud.',
            'correlation_id' => 'practica33-states-503',
        ]);
        $this->assertStringNotContainsString('private state detail', $response->getContent());
        Http::assertSentCount(2);
    }

    public function test_it_rejects_malformed_upstream_data_with_a_controlled_502(): void
    {
        Http::fake([
            self::USERS_URL.'/usuarios/7' => Http::response(['id' => 'not-an-integer', 'nombre' => 'Ana']),
            self::STATES_URL.'/estados/101' => Http::response(['solicitud_id' => 101, 'estado' => 'pendiente']),
        ]);

        $response = $this->getJson('/api/resumen-solicitud/101');

        $response->assertStatus(502)->assertJson([
            'mensaje' => 'No fue posible completar el resumen de la solicitud.',
        ]);
        Http::assertSentCount(1);
    }
}
