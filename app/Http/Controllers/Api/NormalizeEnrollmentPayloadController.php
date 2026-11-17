<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NormalizeEnrollmentPayloadRequest;
use App\Services\Practica23\CarlosChile\EnrollmentPayloadTranslator;
use Illuminate\Http\JsonResponse;

class NormalizeEnrollmentPayloadController extends Controller
{
    public function __invoke(
        NormalizeEnrollmentPayloadRequest $request,
        EnrollmentPayloadTranslator $translator,
    ): JsonResponse {
        $input = $request->validated();
        $normalized = $translator->translate($input['source'], $input['payload']);

        return response()->json(['data' => [
            'course_slug' => $normalized->courseSlug,
            'cohort_code' => $normalized->cohortCode,
        ]]);
    }
}
