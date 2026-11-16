<?php

namespace App\Http\Controllers\Api;

use App\Data\Enrollment\EnrollCourseData;
use App\Data\Enrollment\UpdateEnrollmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Services\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    public function index(Request $request, EnrollmentService $service): AnonymousResourceCollection
    {
        return EnrollmentResource::collection(
            $service->listForUser((string) $request->user()->getAuthIdentifier()),
        );
    }

    public function store(StoreEnrollmentRequest $request, EnrollmentService $service): JsonResponse
    {
        $enrollment = $service->createForUser(
            (string) $request->user()->getAuthIdentifier(),
            EnrollCourseData::fromValidated($request->validated()),
        );

        return (new EnrollmentResource($enrollment))
            ->response()
            ->setStatusCode($enrollment->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Request $request, string $enrollment, EnrollmentService $service): EnrollmentResource
    {
        return new EnrollmentResource(
            $service->findForUser((string) $request->user()->getAuthIdentifier(), $enrollment),
        );
    }

    public function update(
        UpdateEnrollmentRequest $request,
        string $enrollment,
        EnrollmentService $service,
    ): EnrollmentResource {
        return new EnrollmentResource($service->updateForUser(
            (string) $request->user()->getAuthIdentifier(),
            $enrollment,
            UpdateEnrollmentData::fromValidated($request->validated()),
        ));
    }

    public function destroy(Request $request, string $enrollment, EnrollmentService $service): Response
    {
        $service->deleteForUser((string) $request->user()->getAuthIdentifier(), $enrollment);

        return response()->noContent();
    }
}
