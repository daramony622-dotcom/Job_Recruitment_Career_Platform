<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users with filters & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->paginate(
            $request->only(['role', 'search']),
            $request->integer('per_page', 20)
        );

        return response()->json([
            'status' => 'success',
            'data'   => $users
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->input('role') === 'job_seeker') {
            $request->merge(['role' => 'user']);
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:6'],
            'role'      => ['sometimes', 'string', 'in:admin,hr,user,job_seeker'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully.',
            'data'    => $user->load('profile')
        ], 201);
    }

    /**
     * Display the specified user details.
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserDetails($id);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $user
        ]);
    }

    /**
     * Update the specified user role. Admin can only edit the user role.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Only administrators can change user roles.');
        }

        if ($request->input('role') === 'job_seeker') {
            $request->merge(['role' => 'user']);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,hr,user,job_seeker'],
        ]);

        $updatedUser = $this->userService->updateUser($user, ['role' => $validated['role']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User role updated successfully.',
            'data'    => $updatedUser
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->is($user)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You cannot delete the account currently being used.'
            ], 422);
        }

        $this->userService->deleteUser($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'User deleted successfully.'
        ]);
    }
}
