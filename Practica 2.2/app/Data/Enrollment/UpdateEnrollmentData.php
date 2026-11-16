<?php

namespace App\Data\Enrollment;

final readonly class UpdateEnrollmentData
{
    public function __construct(
        public ?string $courseSlug,
        public ?string $cohortCode,
        public bool $hasCohortCode,
    ) {}

    /** @param array{course_slug?: string, cohort_code?: string|null} $validated */
    public static function fromValidated(array $validated): self
    {
        return new self(
            courseSlug: $validated['course_slug'] ?? null,
            cohortCode: $validated['cohort_code'] ?? null,
            hasCohortCode: array_key_exists('cohort_code', $validated),
        );
    }
}
