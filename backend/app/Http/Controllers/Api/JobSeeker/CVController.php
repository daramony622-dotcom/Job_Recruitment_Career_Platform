<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCVRequest;
use App\Http\Requests\UpdateCVRequest;
use App\Http\Resources\CVResource;
use App\Models\CV;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class CVController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CV::class, 'cv');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = CV::query();

        if ($user->hasRole('admin')) {
            // Admins can see all CVs
        } else {
            // Job seekers only see their own CVs
            $query->where('user_id', $user->id);
        }

        $cvs = $query->latest()->get();

        return CVResource::collection($cvs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCVRequest $request): CVResource
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('cvs', 'public');
        }

        $data['user_id'] = $user->id;

        // If this CV is set as primary, unset others for this user
        if (!empty($data['is_primary']) && $data['is_primary']) {
            CV::where('user_id', $user->id)->update(['is_primary' => false]);
        }

        $cv = CV::create($data);

        return new CVResource($cv);
    }

    /**
     * Display the specified resource.
     */
    public function show(CV $cv): CVResource
    {
        return new CVResource($cv);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCVRequest $request, CV $cv): CVResource
    {
        $data = $request->validated();

        if ($request->hasFile('file_path')) {
            // Delete old file if exists
            if ($cv->file_path && Storage::disk('public')->exists($cv->file_path)) {
                Storage::disk('public')->delete($cv->file_path);
            }
            $data['file_path'] = $request->file('file_path')->store('cvs', 'public');
        }

        if (!empty($data['is_primary']) && $data['is_primary']) {
            CV::where('user_id', $cv->user_id)->update(['is_primary' => false]);
        }

        $cv->update($data);

        return new CVResource($cv);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CV $cv): JsonResponse
    {
        if ($cv->file_path && Storage::disk('public')->exists($cv->file_path)) {
            Storage::disk('public')->delete($cv->file_path);
        }

        $cv->delete();

        return response()->json([
            'message' => 'CV deleted successfully.'
        ]);
    }
}