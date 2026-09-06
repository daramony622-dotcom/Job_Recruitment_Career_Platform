<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeeker\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): ProfileResource
    {
        $profile = $request->user()->profile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $profile->load(['user', 'educations', 'experiences']);

        return new ProfileResource($profile);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->validated(),
        );

        $profile->load(['user', 'educations', 'experiences']);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new ProfileResource($profile),
        ]);
    }
}
