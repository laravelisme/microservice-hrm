<?php

namespace App\Http\Controllers\LokasiKerja;

use App\Http\Controllers\Controller;
use App\Http\Requests\LokasiKerja\LokasiKerjaStoreFormRequest;
use App\Http\Requests\LokasiKerja\LokasiKerjaUpdateFormRequest;
use App\Models\MasterData\LokasiKerja;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LokasiKerjaController extends Controller
{
    private LokasiKerja $lokasiKerja;

    public function __construct(LokasiKerja $lokasiKerja)
    {
        $this->lokasiKerja = $lokasiKerja;
    }

    public function index(Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {

            $searchName = trim((string) $request->query('searchName', ''));
            $perPage    = (int) $request->query('perPage', 10);
            $perPage    = max(1, min($perPage, 100));

            $query = $this->lokasiKerja->newQuery();

            if ($searchName) {
                $query->where(function ($query) use ($searchName) {
                    if ($searchName) {
                        $query->where('name', 'like', '%' . $searchName . '%');
                    }
                });
            }

            $lokasiKerja = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

            return $this->successResponse($lokasiKerja, 'Data Lokasi Kerja berhasil diambil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function store(LokasiKerjaStoreFormRequest $request)
    {
        try {

            $data = $request->validated();
            $data['created_by'] = JwtHelper::getClaim($request, 'name');

            $lokasiKerja = $this->lokasiKerja->create($data);

            return $this->successResponse($lokasiKerja, 'Data Lokasi Kerja berhasil dibuat', 201);


        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function show($id)
    {
        try {
            $lokasiKerja = $this->lokasiKerja->find($id);

            if (!$lokasiKerja) {
                return $this->errorResponse('Data Lokasi Kerja tidak ditemukan', 404);
            }

            return $this->successResponse($lokasiKerja, 'Data Lokasi Kerja berhasil diambil', 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function update(LokasiKerjaUpdateFormRequest $request, $id)
    {
        try {

            $data = $request->validated();
            $data['updated_by'] = JwtHelper::getClaim($request, 'name');

            $lokasiKerja = $this->lokasiKerja->find($id);

            if (!$lokasiKerja) {
                return $this->errorResponse('Data Lokasi Kerja tidak ditemukan', 404);
            }

            $lokasiKerja->update($data);

            return $this->successResponse($lokasiKerja, 'Data Lokasi Kerja berhasil diperbarui', 200);

        } catch (\Throwable $e) {
                Log::error($e->getMessage());
                if (app()->environment('local')) {
                    return $this->errorResponse($e->getMessage(), 500);
                }
                return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $lokasiKerja = $this->lokasiKerja->find($id);

            if (!$lokasiKerja) {
                return $this->errorResponse('Data Lokasi Kerja tidak ditemukan', 404);
            }

            $lokasiKerja->delete();

            return $this->successResponse(null, 'Data Lokasi Kerja berhasil dihapus', 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }
}
