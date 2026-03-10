<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CompanyStoreFormRequest;
use App\Http\Requests\Company\CompanyUpdateFormRequest;
use App\Models\MasterData\Company;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    private Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function index(Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return $this->errorResponse('Anda tidak memiliki akses untuk melihat data company', 403);
        }

        try {

            $searchName = trim((string) $request->query('searchName', ''));
            $searchLevel = trim((string) $request->query('searchLevel', ''));
            $perPage    = (int) $request->query('perPage', 10);
            $perPage    = max(1, min($perPage, 100));

            $query = $this->company->newQuery();

            if ($searchName || $searchLevel) {
                $query->where(function ($query) use ($searchName, $searchLevel) {
                    if ($searchName) {
                        $query->where('company_name', 'like', '%' . $searchName . '%');
                    }
                    if ($searchLevel) {
                        $query->where('level', $searchLevel);
                    }
                });
            }

            $companies = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
            return $this->successResponse($companies, 'Data Company berhasil dimuat', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function store(CompanyStoreFormRequest $request)
    {
        try {

            $data = $request->validated();
            $data['created_by'] = JwtHelper::getClaim($request, 'name');

            $company = $this->company->create($data);

            if ($company) {
                return $this->successResponse($company, 'Data company berhasil di simpan', 201);
            } else {
                return $this->errorResponse('Gagal menyimpan data company', 500);
            }

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function show(Request $request, $id)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return $this->errorResponse('Anda tidak memiliki akses untuk melihat data company', 403);
        }

        try {
            $company = $this->company->find($id);

            if ($company) {
                return $this->successResponse($company, 'Data company berhasil dimuat', 200);
            } else {
                return $this->errorResponse('Data company tidak ditemukan', 404);
            }

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function update(CompanyUpdateFormRequest $request, $id)
    {
        try {
            $company = $this->company->find($id);

            if (!$company) {
                return $this->errorResponse('Data company tidak ditemukan', 404);
            }

            $data = $request->validated();
            $data['updated_by'] = JwtHelper::getClaim($request, 'name');

            $updated = $company->update($data);

            if ($updated) {
                return $this->successResponse($company, 'Data company berhasil di update', 200);
            } else {
                return $this->errorResponse('Gagal mengupdate data company', 500);
            }

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function destroy(Request $request, $id)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return $this->errorResponse('Anda tidak memiliki akses untuk menghapus data company', 403);
        }

        try {
            $company = $this->company->find($id);

            if (!$company) {
                return $this->errorResponse('Data company tidak ditemukan', 404);
            }

            $deleted = $company->delete();

            if ($deleted) {
                return $this->successResponse(null, 'Data company berhasil dihapus', 200);
            } else {
                return $this->errorResponse('Gagal menghapus data company', 500);
            }

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }
}
