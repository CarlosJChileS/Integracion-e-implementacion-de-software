<?php

namespace App\Services;

use App\Data\Enrollment\EnrollCourseData;
use App\Data\Enrollment\UpdateEnrollmentData;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function listForUser(string $userId): LengthAwarePaginator
    {
        return Enrollment::query()
            ->where('user_id', $userId)
            ->orderByDesc('enrolled_at')
            ->paginate(15);
    }

    public function createForUser(string $userId, EnrollCourseData $data): Enrollment
    {
        $course = Course::query()->where('slug', $data->courseSlug)->firstOrFail();
        $cohort = $data->cohortCode === null
            ? null
            : Cohort::query()->where('code', $data->cohortCode)->firstOrFail();

        return Enrollment::query()->firstOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $course->id,
            ],
            ['cohort_id' => $cohort?->id, 'enrolled_at' => now()],
        );
    }

    public function findForUser(string $userId, string $enrollmentId): Enrollment
    {
        return Enrollment::query()
            ->where('user_id', $userId)
            ->whereKey($enrollmentId)
            ->firstOrFail();
    }

    public function updateForUser(
        string $userId,
        string $enrollmentId,
        UpdateEnrollmentData $data,
    ): Enrollment {
        return DB::transaction(function () use ($userId, $enrollmentId, $data): Enrollment {
            $enrollment = $this->findForUser($userId, $enrollmentId);

            if ($data->courseSlug !== null) {
                $course = Course::query()->where('slug', $data->courseSlug)->firstOrFail();

                $duplicateExists = Enrollment::query()
                    ->where('user_id', $userId)
                    ->where('course_id', $course->id)
                    ->where('id', '!=', $enrollment->id)
                    ->exists();

                if ($duplicateExists) {
                    throw ValidationException::withMessages([
                        'course_slug' => 'Ya existe una inscripción para este curso.',
                    ]);
                }

                $enrollment->course_id = $course->id;
            }

            if ($data->hasCohortCode) {
                $cohort = $data->cohortCode === null
                    ? null
                    : Cohort::query()->where('code', $data->cohortCode)->firstOrFail();
                $enrollment->cohort_id = $cohort?->id;
            }

            try {
                $enrollment->save();
            } catch (QueryException $exception) {
                if (! $this->isDuplicateEnrollmentException($exception)) {
                    throw $exception;
                }

                throw ValidationException::withMessages([
                    'course_slug' => 'Ya existe una inscripción para este curso.',
                ]);
            }

            return $enrollment->refresh();
        });
    }

    public function deleteForUser(string $userId, string $enrollmentId): void
    {
        $this->findForUser($userId, $enrollmentId)->delete();
    }

    private function isDuplicateEnrollmentException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'enrollments_user_id_course_id_unique')
            || str_contains($message, 'unique constraint failed: enrollments.user_id, enrollments.course_id');
    }
}
