<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NormalizeEnrollmentPayloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'source' => ['required', 'string', Rule::in(['legacy', 'portal'])],
            'payload' => ['required', 'array'],
        ];
    }
}
