<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnrollmentContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_json_creates_enrollment_and_returns_serialized_resource(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $course = Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        Cohort::create(['name' => 'Charla de prueba', 'code' => 'ILIA-DEMO']);

        $response = $this->postJson('/api/enrollments', [
            'course_slug' => 'prompting-para-docentes',
            'cohort_code' => 'ILIA-DEMO',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'course_id', 'enrolled_at', 'cohort_id']])
            ->assertJsonPath('data.course_id', $course->id)
            ->assertJsonPath('data.cohort_id', Cohort::first()->id);

        $this->assertNotNull($response->json('data.enrolled_at'));
        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_missing_course_slug_returns_422_without_persisting(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/enrollments', ['cohort_code' => 'ILIA-DEMO'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('course_slug');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_wrong_course_slug_type_returns_422_without_persisting(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/enrollments', ['course_slug' => 42])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('course_slug');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_cohort_code_is_optional_and_can_be_null(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);

        $this->postJson('/api/enrollments', [
            'course_slug' => 'prompting-para-docentes',
            'cohort_code' => null,
        ])->assertCreated()->assertJsonPath('data.cohort_id', null);
    }

    public function test_unknown_cohort_code_returns_422_without_persisting(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);

        $this->postJson('/api/enrollments', [
            'course_slug' => 'prompting-para-docentes',
            'cohort_code' => 'NO-EXISTE',
        ])->assertUnprocessable()->assertJsonValidationErrors('cohort_code');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_request_requires_authentication(): void
    {
        $this->postJson('/api/enrollments', ['course_slug' => 'prompting-para-docentes'])
            ->assertUnauthorized();

        $this->getJson('/api/enrollments')->assertUnauthorized();
        $this->getJson('/api/enrollments/any-id')->assertUnauthorized();
        $this->patchJson('/api/enrollments/any-id', ['cohort_code' => null])->assertUnauthorized();
        $this->deleteJson('/api/enrollments/any-id')->assertUnauthorized();
    }

    public function test_repeating_enrollment_does_not_create_a_duplicate(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Course::create(['slug' => 'prompting-para-docentes', 'name' => 'Prompting para docentes']);
        $payload = ['course_slug' => 'prompting-para-docentes'];

        $first = $this->postJson('/api/enrollments', $payload)->assertCreated();
        $second = $this->postJson('/api/enrollments', $payload)->assertOk();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_index_returns_only_the_authenticated_users_enrollments(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $course = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
        Enrollment::create(['user_id' => $otherUser->id, 'course_id' => $course->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/enrollments')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_update_and_delete_cannot_access_another_users_enrollment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $course = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        $enrollment = Enrollment::create(['user_id' => $owner->id, 'course_id' => $course->id]);
        Sanctum::actingAs($otherUser);

        $this->getJson('/api/enrollments/'.$enrollment->id)->assertNotFound();
        $this->patchJson('/api/enrollments/'.$enrollment->id, ['course_slug' => 'curso-a'])->assertNotFound();
        $this->deleteJson('/api/enrollments/'.$enrollment->id)->assertNotFound();

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);
    }

    public function test_user_can_update_enrollment_and_clear_optional_cohort(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        $nextCourse = Course::create(['slug' => 'curso-b', 'name' => 'Curso B']);
        $cohort = Cohort::create(['name' => 'Charla A', 'code' => 'COHORTE-A']);
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'cohort_id' => $cohort->id,
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/enrollments/'.$enrollment->id)
            ->assertOk()
            ->assertJsonPath('data.id', $enrollment->id);

        $this->patchJson('/api/enrollments/'.$enrollment->id, [
            'course_slug' => 'curso-b',
            'cohort_code' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.course_id', $nextCourse->id)
            ->assertJsonPath('data.cohort_id', null);
    }

    public function test_invalid_update_returns_422_without_changing_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/enrollments/'.$enrollment->id, ['course_slug' => 'no-existe'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('course_slug');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_update_rejects_a_duplicate_course_enrollment(): void
    {
        $user = User::factory()->create();
        $firstCourse = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        $secondCourse = Course::create(['slug' => 'curso-b', 'name' => 'Curso B']);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $firstCourse->id]);
        $secondEnrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $secondCourse->id]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/enrollments/'.$secondEnrollment->id, ['course_slug' => 'curso-a'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('course_slug');

        $this->assertDatabaseHas('enrollments', [
            'id' => $secondEnrollment->id,
            'course_id' => $secondCourse->id,
        ]);
    }

    public function test_unique_constraint_race_returns_validation_error_without_changing_enrollment(): void
    {
        $user = User::factory()->create();
        $currentCourse = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        $targetCourse = Course::create(['slug' => 'curso-b', 'name' => 'Curso B']);
        $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $currentCourse->id]);
        Sanctum::actingAs($user);

        DB::unprepared(sprintf(
            'CREATE TRIGGER concurrent_enrollment_insert BEFORE UPDATE OF course_id ON enrollments '.
            "WHEN NEW.id = '%s' BEGIN INSERT INTO enrollments (id, user_id, course_id, enrolled_at) ".
            "VALUES ('%s', '%s', NEW.course_id, CURRENT_TIMESTAMP); END",
            $enrollment->id,
            '00000000-0000-4000-8000-000000000001',
            $user->id,
        ));

        $this->patchJson('/api/enrollments/'.$enrollment->id, ['course_slug' => 'curso-b'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('course_slug');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'course_id' => $currentCourse->id,
        ]);
        $this->assertDatabaseMissing('enrollments', ['course_id' => $targetCourse->id]);
    }

    public function test_empty_update_is_rejected_and_delete_removes_own_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['slug' => 'curso-a', 'name' => 'Curso A']);
        $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/enrollments/'.$enrollment->id, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('course_slug');

        $this->deleteJson('/api/enrollments/'.$enrollment->id)->assertNoContent();
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }
}
