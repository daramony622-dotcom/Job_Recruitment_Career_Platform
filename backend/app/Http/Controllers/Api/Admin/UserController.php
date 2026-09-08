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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->input('role') === 'job_seeker') {
            $request->merge(['role' => 'user']);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['sometimes', 'string', 'in:admin,hr,user'],
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully.',
            'data'    => $user->load('profile')
        ], 201);
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        if ($request->has('role') && $request->user()?->role !== 'admin') {
            abort(403, 'Only an authenticated administrator can change user roles.');
        }

        if ($request->input('role') === 'job_seeker') {
            $request->merge(['role' => 'user']);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,hr,user'],
        ]);

        $user = $this->userService->updateUser($user, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'User updated successfully.',
            'data'    => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->is($user)) {
            abort(422, 'You cannot delete the account currently being used.');
        }

        $user->delete();

        return response()->json([
            'status'
               => 'success',
            'message' => 'User deleted successfully.'
        ]);
    }
}
