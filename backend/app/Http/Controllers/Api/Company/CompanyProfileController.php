namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function show(Request $request): JsonResponse
    {
        // Get the authenticated user's company
        $company = $request->user()->company; // Assuming a hasOne('company') relation on User

        if (!$company) {
            return response()->json(['message' => 'Company profile not found.'], 404);
        }

        return response()->json([
            'data' => $company
        ]);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        // Ensure user doesn't already have a company
        if ($request->user()->company) {
            return response()->json(['message' => 'Company profile already exists.'], 422);
        }

        $company = $this->companyService->store($request->validated(), $request->user());

        return response()->json([
            'message' => 'Company profile created successfully.',
            'data' => $company
        ], 201);
    }

    public function update(UpdateCompanyRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json(['message' => 'Company profile not found.'], 404);
        }

        $updatedCompany = $this->companyService->update($request->validated(), $company);

        return response()->json([
            'message' => 'Company profile updated successfully.',
            'data' => $updatedCompany
        ]);
    }
}