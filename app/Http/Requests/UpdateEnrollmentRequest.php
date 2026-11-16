<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'course_slug' => ['sometimes', 'string', 'max:160', 'exists:courses,slug'],
            'cohort_code' => ['sometimes', 'nullable', 'string', 'max:32', 'exists:cohorts,code'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->hasAny(['course_slug', 'cohort_code'])) {
                $validator->errors()->add('course_slug', 'Debe enviar al menos un campo para actualizar.');
            }
        }];
    }
}
