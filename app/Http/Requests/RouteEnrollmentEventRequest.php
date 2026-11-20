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
        return [
            'id' => ['required', 'string', 'uuid'],
            'type' => ['required', 'string', 'max:80'],
            'priority' => ['required', 'string', 'in:baja,normal,alta'],
            'data' => ['required', 'array'],
            'data.enrollment_id' => ['required', 'uuid'],
            'data.course_slug' => ['required', 'string', 'max:160'],
        ];
    }
}
