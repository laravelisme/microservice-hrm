<?php

namespace App\Http\Controllers\GrupJamKerja;

use App\Http\Controllers\Controller;
use App\Http\Requests\GrupJamKerja\GrupJamKerjaStoreFormRequest;
use App\Models\MasterData\GrupJamKerja;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GrupJamKerjaController extends Controller
{
    private GrupJamKerja $grupJamKerja;

    public function __construct(GrupJamKerja $grupJamKerja)
    {
        $this->grupJamKerja = $grupJamKerja;
    }

    public function index(Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {

            $searchName = trim((string) $request->query('searchName', ''));
            $perPage = (int) $request->query('perPage', 10);
            $perPage = max(1, min($perPage, 100));

            $query = $this->grupJamKerja->newQuery();

            if ($searchName !== '') {
                $query->where('name', 'like', '%' . $searchName . '%');
            }

            $grupJamKerjas = $query->orderByDesc('id')
                ->paginate($perPage)
                ->withQueryString();

            return $this->successResponse($grupJamKerjas, 'Grup Jam Kerja berhasil diambil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function store(GrupJamKerjaStoreFormRequest $request)
    {
        try {

            $data = $request->validated();
            $grupJamKerja = $this->grupJamKerja->create($data);

            return $this->successResponse($grupJamKerja, 'Grup jam kerja created successfully.', 201);

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
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {
            $grupJamKerja = $this->grupJamKerja->findOrFail($id);
            return $this->successResponse($grupJamKerja, 'Grup jam kerja retrieved successfully.', 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function update(GrupJamKerjaStoreFormRequest $request, $id)
    {
        try {

            $data = $request->validated();
            $grupJamKerja = $this->grupJamKerja->findOrFail($id);
            $grupJamKerja->update($data);

            return $this->successResponse($grupJamKerja, 'Grup jam kerja updated successfully.', 200);

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
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {
            $grupJamKerja = $this->grupJamKerja->findOrFail($id);
            $grupJamKerja->delete();
            return $this->successResponse(null, 'Grup jam kerja deleted successfully.', 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }
}
