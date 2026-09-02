<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Http\Resources\EducationResource;
use App\Models\Education;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EducationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Education::class, 'education');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $user = request()->user();

        $query = Education::query()->with(['user']);

        if ($user->hasRole('admin')) {
            // Admin can see all education records
        } elseif ($user->hasRole('company') || $user->hasRole('hr')) {
            // Company/HR can see education records
        } else {
            // Job seekers can only see their own education records
            $query->where('user_id', $user->id);
        }

        $educations = $query->latest('start_date')->get();

        return EducationResource::collection($educations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEducationRequest $request): EducationResource
    {
        $data = $request->validated();

        if (!isset($data['user_id']) && !request()->user()->hasRole('admin')) {
            $data['user_id'] = request()->user()->id;
        }

        $education = Education::create($data);
        $education->load(['user']);

        return new EducationResource($education);
    }

    /**
     * Display the specified resource.
     */
    public function show(Education $education): EducationResource
    {
        $education->load(['user']);

        return new EducationResource($education);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEducationRequest $request, Education $education): EducationResource
    {
        $education->update($request->validated());
        $education->load(['user']);

        return new EducationResource($education);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Education $education): JsonResponse
    {
        $education->delete();

        return response()->json([
            'message' => 'Education deleted successfully.'
        ]);
    }
}