<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employees = User::query()
            ->whereIn('role', ['user', 'job_seeker'])
            ->with('profile')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(fn ($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $employees]);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        abort_unless($user->hasRole(['user', 'job_seeker']), 404);

        return response()->json(['status' => 'success', 'data' => $user->load([
            'profile',
            'skills',
            'cvs',
            'educations',
            'experiences',
            'applications.jobPost.company',
        ])]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_unless($user->hasRole(['user', 'job_seeker']), 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Employee updated successfully.', 'data' => $user->fresh('profile')]);
    }

}