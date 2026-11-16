<?php

namespace App\Data\Enrollment;

final readonly class EnrollCourseData
{
    public function __construct(
        public string $courseSlug,
        public ?string $cohortCode,
    ) {}

    /** @param array{course_slug: string, cohort_code?: string|null} $validated */
    public static function fromValidated(array $validated): self
    {
        return new self(
            courseSlug: $validated['course_slug'],
            cohortCode: $validated['cohort_code'] ?? null,
        );
    }
}
