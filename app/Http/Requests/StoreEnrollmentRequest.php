<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'course_slug' => ['required', 'string', 'max:160', 'exists:courses,slug'],
            'cohort_code' => ['sometimes', 'nullable', 'string', 'max:32', 'exists:cohorts,code'],
        ];
    }
}
