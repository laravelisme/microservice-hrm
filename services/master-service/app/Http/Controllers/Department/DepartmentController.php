<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepartmentStoreFormRequest;
use App\Http\Requests\Department\DepartmentUpdateFormRequest;
use App\Models\MasterData\Company;
use App\Models\MasterData\Department;
use App\Models\MCompany;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    private Department $department;

    public function __construct(Department $department)
    {
        $this->department = $department;
    }

    public function index(Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {
            $searchName      = trim((string) $request->query('searchName', ''));
            $searchIsHr      = (string) $request->query('searchIsHr', '');
            $searchCompanyId = (string) $request->query('searchCompanyId', '');
            $perPage         = (int) $request->query('perPage', 10);
            $perPage         = max(1, min($perPage, 100));

            $query = $this->department->newQuery()
                ->with('company:id,company_name')
                ->select(['id','department_name','company_id','is_hr','created_at','updated_at']);

            if ($searchName !== '') {
                $query->where('department_name', 'like', '%' . $searchName . '%');
            }
            if ($searchIsHr !== '') {
                $query->where('is_hr', (int) $searchIsHr);
            }
            if ($searchCompanyId !== '') {
                $query->where('company_id', (int) $searchCompanyId);
            }

            $departments = $query->orderByDesc('id')
                ->paginate($perPage)
                ->withQueryString();

            $selectedCompany = null;
            if ($searchCompanyId !== '') {
                $selectedCompany = Company::select('id','company_name')->find((int)$searchCompanyId);
            }

            $data = [
                'departments' => $departments,
                'selectedCompany' => $selectedCompany,
            ];

            return $this->successResponse($data, 'Department data berhasil dimuat', 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function companyOptions(Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {
            $term    = trim((string) $request->get('q', $request->get('term', '')));
            $page    = max(1, (int) $request->get('page', 1));
            $perPage = (int) $request->get('perPage', 20);
            $perPage = max(1, min($perPage, 50));

            $q = Company::query()
                ->select('id', 'company_name');

            if ($term !== '') {
                $q->where('company_name', 'like', '%' . $term . '%');
            }

            $paginator = $q->orderBy('company_name')
                ->paginate($perPage, ['*'], 'page', $page);

            $results = $paginator->getCollection()->map(fn ($c) => [
                'id'   => $c->id,
                'text' => $c->company_name,
            ])->values();

            $data = [
                'results' => $results,
                'pagination' => [
                    'more' => $paginator->hasMorePages(),
                ],
            ];

            return $this->successResponse($data, 'Department data berhasil dimuat', 200);
        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function store(DepartmentStoreFormRequest $request)
    {
        try {

            $data = $request->validated();

            $data['created_by'] = JwtHelper::getClaim($request, 'name');

            $department = $this->department->newQuery()->create($data);

            return $this->successResponse($department, 'Department berhasil dibuat', 201);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function update(DepartmentUpdateFormRequest $request, $id)
    {
        try {

            $data = $request->validated();

            $data['updated_by'] = JwtHelper::getClaim($request, 'name');

            $department = $this->department->newQuery()->findOrFail($id);
            $department->update($data);

            return $this->successResponse($department, 'Department berhasil diperbarui', 200);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function show($id)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {
            $department = $this->department->newQuery()
                ->with('company:id,company_name')
                ->findOrFail($id);

            return $this->successResponse($department, 'Department data berhasil dimuat', 200);
        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $department = $this->department->newQuery()->findOrFail($id);
            $department->delete();

            return $this->successResponse(null, 'Department berhasil dihapus', 200);
        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

}
