<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobCategoryRequest;
use App\Http\Requests\Admin\UpdateJobCategoryRequest;
use App\Models\JobCategory;
use App\Services\JobCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobCategoryController extends Controller
{
    public function __construct(private readonly JobCategoryService $service)
    {
    }

    // -------------------------------------------------------------------------
    // GET /admin/job-categories
    // Paginated list with optional ?search=, ?is_active=, ?parent_id=
    // -------------------------------------------------------------------------
    public function index(Request $request): JsonResponse
    {
        $filters    = $request->only(['search', 'is_active', 'parent_id']);
        $categories = $this->service->paginate($filters, $request->integer('per_page', 15));

        return response()->json($categories);
    }

    // -------------------------------------------------------------------------
    // POST /admin/job-categories
    // -------------------------------------------------------------------------
    public function store(StoreJobCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'data'    => $category->load('parent'),
        ], 201);
    }

    // -------------------------------------------------------------------------
    // GET /admin/job-categories/{job_category}
    // -------------------------------------------------------------------------
    public function show(JobCategory $jobCategory): JsonResponse
    {
        return response()->json([
            'data' => $jobCategory->load(['parent', 'children']),
        ]);
    }

    // -------------------------------------------------------------------------
    // PUT /admin/job-categories/{job_category}
    // -------------------------------------------------------------------------
    public function update(UpdateJobCategoryRequest $request, JobCategory $jobCategory): JsonResponse
    {
        $category = $this->service->update($jobCategory, $request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data'    => $category->load('parent'),
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /admin/job-categories/{job_category}
    // -------------------------------------------------------------------------
    public function destroy(JobCategory $jobCategory): JsonResponse
    {
        $this->service->delete($jobCategory);

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    // -------------------------------------------------------------------------
    // PATCH /admin/job-categories/{job_category}/toggle-active
    // -------------------------------------------------------------------------
    public function toggleActive(JobCategory $jobCategory): JsonResponse
    {
        $category = $this->service->toggleActive($jobCategory);

        return response()->json([
            'message' => 'Category status toggled successfully.',
            'data'    => $category,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/job-categories/reorder
    // Body: { items: [{ id: 1, sort_order: 0 }, ...] }
    // -------------------------------------------------------------------------
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items'              => ['required', 'array'],
            'items.*.id'         => ['required', 'integer', 'exists:job_categories,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->service->reorder($request->input('items'));

        return response()->json(['message' => 'Categories reordered successfully.']);
    }

    // -------------------------------------------------------------------------
    // GET /admin/job-categories/tree
    // Full nested tree (public + cached)
    // -------------------------------------------------------------------------
    public function tree(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getTree(),
        ]);
    }
}