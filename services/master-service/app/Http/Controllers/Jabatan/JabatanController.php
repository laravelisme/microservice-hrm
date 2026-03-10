<?php

namespace App\Http\Controllers\Jabatan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Jabatan\JabatanStoreFormRequest;
use App\Http\Requests\Jabatan\JabatanUpdateFormRequest;
use App\Models\MasterData\Jabatan;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JabatanController extends Controller
{
    private Jabatan $jabatan;

    public function __construct(Jabatan $jabatan)
    {
        $this->jabatan = $jabatan;
    }

    public function index (Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {
            $searchName  = trim((string) $request->query('searchName', ''));
            $searchKode  = trim((string) $request->query('searchKode', ''));
            $searchLevel = trim((string) $request->query('searchLevel', ''));
            $perPage     = (int) $request->query('perPage', 10);
            $perPage     = max(1, min($perPage, 100));

            $query = $this->jabatan->newQuery();

            if ($searchName || $searchKode || $searchLevel) {
                $query->where(function ($query) use ($searchName, $searchKode, $searchLevel) {
                    if ($searchName) {
                        $query->where('name', 'like', '%' . $searchName . '%');
                    }
                    if ($searchLevel) {
                        $query->where('level', $searchLevel);
                    }
                    if ($searchKode) {
                        $query->where('kode', $searchKode);
                    }
                });
            }

            $jabatans = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

            return $this->successResponse($jabatans, 'Data Jabatan Berhasil Diambil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }


    public function store(JabatanStoreFormRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = JwtHelper::getClaim($request, 'name');

            DB::beginTransaction();

            $jabatan = $this->jabatan->create($data);

            DB::commit();

            return $this->successResponse($jabatan, 'Jabatan berhasil dibuat', 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[JabatanController@store] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

    public function show(Request $request, $id)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {

            $jabatan = $this->jabatan->find($id);

            if (!$jabatan) {
                return $this->errorResponse('Jabatan tidak ditemukan', 404);
            }

            return $this->successResponse($jabatan, 'Data Jabatan Berhasil Diambil', 200);

        } catch (\Throwable $e) {
                if (app()->environment('local')) {
                    return $this->errorResponse($e->getMessage(), 500);
                }
                Log::error('[JabatanController@show] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

    public function update(JabatanUpdateFormRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $data['updated_by'] = JwtHelper::getClaim($request, 'name');

            DB::beginTransaction();

            $jabatan = $this->jabatan->find($id);

            if (!$jabatan) {
                return $this->errorResponse('Jabatan tidak ditemukan', 404);
            }

            $jabatan->update($data);

            DB::commit();

            return $this->successResponse($jabatan, 'Jabatan berhasil diperbarui', 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[JabatanController@update] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
         }
    }

    public function destroy(Request $request, $id)
    {
        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {

            $jabatan = $this->jabatan->find($id);

            if (!$jabatan) {
                return $this->errorResponse('Jabatan tidak ditemukan', 404);
            }

            $jabatan->delete();

            return $this->successResponse(null, 'Jabatan berhasil dihapus', 200);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[JabatanController@destroy] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
         }
    }

}
