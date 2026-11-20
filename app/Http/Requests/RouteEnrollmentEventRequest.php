<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteEnrollmentEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $type = $this->input('type');
        $requiresCourse = in_array($type, ['enrollment.created', 'enrollment.updated'], true);
        $requiresEnrollment = in_array($type, ['enrollment.updated', 'enrollment.cancelled'], true);

        return [
            'id' => ['required', 'string', 'uuid'],
            'type' => ['required', 'string', 'max:80'],
            'priority' => ['required', 'string', 'in:baja,normal,alta'],
            'data' => ['required', 'array'],
            'data.enrollment_id' => [$requiresEnrollment ? 'required' : 'sometimes', 'nullable', 'uuid'],
            'data.course_slug' => array_values(array_filter([
                $requiresCourse ? 'required' : 'sometimes',
                'nullable', 'string', 'max:160', $requiresCourse ? 'exists:courses,slug' : null,
            ])),
            'data.cohort_code' => ['sometimes', 'nullable', 'string', 'max:32', 'exists:cohorts,code'],
        ];
    }
}
