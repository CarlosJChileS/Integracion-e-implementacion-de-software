<?php

namespace App\Services\Practica23\CarlosChile;

use App\Data\Enrollment\EnrollCourseData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class EnrollmentPayloadTranslator
{
    /** @param array<string, mixed> $payload */
    public function translate(string $source, array $payload): EnrollCourseData
    {
        return match ($source) {
            'legacy' => $this->fromLegacy($payload),
            'portal' => $this->fromPortal($payload),
            default => throw ValidationException::withMessages([
                'source' => 'El origen debe ser legacy o portal.',
            ]),
        };
    }

    /** @param array<string, mixed> $payload */
    public function fromLegacy(array $payload): EnrollCourseData
    {
        $this->assertOnlyKeys($payload, ['codigo_curso', 'codigo_paralelo']);

        $input = Validator::make($payload, [
            'codigo_curso' => ['required', 'string', 'max:160'],
            'codigo_paralelo' => ['sometimes', 'nullable', 'string', 'max:32'],
        ], [
            'codigo_curso.required' => 'El código del curso es obligatorio.',
            'codigo_curso.string' => 'El código del curso debe ser texto.',
            'codigo_paralelo.string' => 'El código del paralelo debe ser texto.',
        ])->validate();

        return $this->toCommonContract(
            courseSlug: $input['codigo_curso'],
            cohortCode: $input['codigo_paralelo'] ?? null,
        );
    }

    /** @param array<string, mixed> $payload */
    public function fromPortal(array $payload): EnrollCourseData
    {
        $this->assertOnlyKeys($payload, ['course', 'cohort']);

        $input = Validator::make($payload, [
            'course' => ['required', 'array:slug'],
            'course.slug' => ['required', 'string', 'max:160'],
            'cohort' => ['sometimes', 'nullable', 'array:code'],
            'cohort.code' => ['required_with:cohort', 'string', 'max:32'],
        ], [
            'course.required' => 'El objeto course es obligatorio.',
            'course.array' => 'course solo puede contener el campo slug.',
            'course.slug.required' => 'El campo course.slug es obligatorio.',
            'course.slug.string' => 'course.slug debe ser texto.',
            'cohort.array' => 'cohort solo puede contener el campo code.',
            'cohort.code.required_with' => 'Indica cohort.code o envía cohort como null.',
            'cohort.code.string' => 'cohort.code debe ser texto.',
        ])->validate();

        return $this->toCommonContract(
            courseSlug: $input['course']['slug'],
            cohortCode: $input['cohort']['code'] ?? null,
        );
    }

    /** @param array<string, mixed> $payload
     * @param  list<string>  $allowedKeys
     */
    private function assertOnlyKeys(array $payload, array $allowedKeys): void
    {
        if (array_diff(array_keys($payload), $allowedKeys) !== []) {
            throw ValidationException::withMessages([
                'payload' => 'El origen contiene campos no permitidos o ambiguos.',
            ]);
        }
    }

    private function toCommonContract(string $courseSlug, ?string $cohortCode): EnrollCourseData
    {
        $canonical = [
            'course_slug' => strtolower(trim($courseSlug)),
            'cohort_code' => $cohortCode === null ? null : strtoupper(trim($cohortCode)),
        ];

        $validated = Validator::make($canonical, [
            'course_slug' => [
                'required',
                'string',
                'max:160',
                'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
                Rule::exists('courses', 'slug'),
            ],
            'cohort_code' => [
                'nullable',
                'string',
                'max:32',
                'regex:/\A[A-Z0-9]+(?:-[A-Z0-9]+)*\z/',
                Rule::exists('cohorts', 'code'),
            ],
        ], [
            'course_slug.required' => 'El curso es obligatorio después de la transformación.',
            'course_slug.regex' => 'El slug normalizado debe usar minúsculas, números y guiones simples.',
            'course_slug.exists' => 'El curso normalizado no existe en el catálogo de ILIA.',
            'cohort_code.regex' => 'El código normalizado debe usar mayúsculas, números y guiones simples.',
            'cohort_code.exists' => 'La cohorte normalizada no existe en el catálogo de ILIA.',
        ])->validate();

        return EnrollCourseData::fromValidated($validated);
    }
}
