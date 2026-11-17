<?php

namespace Tests\Feature\Practica23\CarlosChile;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnrollmentPayloadTranslatorTest extends TestCase
{
    use RefreshDatabase;

    private const NORMALIZE_ENDPOINT = '/api/integrations/enrollments/normalize';

    public function test_legacy_and_portal_sources_map_to_the_same_normalized_contract(): void
    {
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        Cohort::create(['name' => 'Cohorte de demostración', 'code' => 'ILIA-DEMO']);
        Sanctum::actingAs(User::factory()->create());

        $legacy = $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'legacy',
            'payload' => [
                'codigo_curso' => ' PROMPTING-PARA-DOCENTES ',
                'codigo_paralelo' => ' ilia-demo ',
            ],
        ])->assertOk()
            ->assertExactJson(['data' => [
                'course_slug' => 'prompting-para-docentes',
                'cohort_code' => 'ILIA-DEMO',
            ]]);

        $portal = $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'portal',
            'payload' => [
                'course' => ['slug' => 'prompting-para-docentes'],
                'cohort' => ['code' => 'ILIA-DEMO'],
            ],
        ])->assertOk()
            ->assertExactJson(['data' => [
                'course_slug' => 'prompting-para-docentes',
                'cohort_code' => 'ILIA-DEMO',
            ]]);

        $this->assertSame($legacy->json('data'), $portal->json('data'));
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_missing_or_non_string_course_values_return_explicit_validation_errors(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'legacy',
            'payload' => ['codigo_paralelo' => 'ILIA-DEMO'],
        ])->assertUnprocessable()->assertJsonValidationErrors('codigo_curso');

        $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'legacy',
            'payload' => ['codigo_curso' => 42],
        ])->assertUnprocessable()->assertJsonValidationErrors('codigo_curso');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_unknown_catalog_values_and_ambiguous_fields_are_rejected(): void
    {
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        Cohort::create(['name' => 'Cohorte de demostración', 'code' => 'ILIA-DEMO']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'legacy',
            'payload' => ['codigo_curso' => 'curso-inexistente'],
        ])->assertUnprocessable()->assertJsonValidationErrors('course_slug');

        $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'legacy',
            'payload' => [
                'codigo_curso' => 'prompting-para-docentes',
                'course_slug' => 'curso-inexistente',
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('payload');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_omitted_optional_cohort_becomes_null_in_the_common_contract(): void
    {
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'legacy',
            'payload' => ['codigo_curso' => 'prompting-para-docentes'],
        ])->assertOk()->assertExactJson(['data' => [
            'course_slug' => 'prompting-para-docentes',
            'cohort_code' => null,
        ]]);

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_normalization_endpoint_requires_authentication(): void
    {
        $this->postJson(self::NORMALIZE_ENDPOINT, [
            'source' => 'legacy',
            'payload' => ['codigo_curso' => 'prompting-para-docentes'],
        ])->assertUnauthorized();
    }
}
