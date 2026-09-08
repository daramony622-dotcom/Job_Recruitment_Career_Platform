<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeeker\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $validated = $request->validated();

        $user->update(collect($validated)->only(['name', 'email'])->all());

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            collect($validated)->except(['name', 'email'])->all(),
        );

        $profile->load(['user', 'educations', 'experiences']);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new ProfileResource($profile),
        ]);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $profile = $request->user()->profile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        if ($profile->avatar && !filter_var($profile->avatar, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $profile->update([
            'avatar' => $request->file('avatar')->store('avatars', 'public'),
        ]);

        $profile->load(['user', 'educations', 'experiences']);

        return response()->json([
            'message' => 'Profile photo updated successfully.',
            'data' => new ProfileResource($profile),
        ]);
    }
}
