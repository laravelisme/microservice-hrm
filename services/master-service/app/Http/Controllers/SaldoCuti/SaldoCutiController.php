<?php

namespace App\Http\Controllers\SaldoCuti;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaldoCuti\SaldoCutiStoreFormRequest;
use App\Http\Requests\SaldoCuti\SaldoCutiUpdateFormRequest;
use App\Models\MasterData\Jabatan;
use App\Models\MasterData\SaldoCuti;
use App\Models\MJabatan;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SaldoCutiController extends Controller
{
    private SaldoCuti $saldoCuti;

    public function __construct(SaldoCuti $saldoCuti)
    {
        $this->saldoCuti = $saldoCuti;
    }

    public function index(Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {

            $searchJenis = trim((string) $request->query('searchJenis', ''));
            $searchJumlah = trim((string) $request->query('searchJumlah', ''));
            $perPage     = (int) $request->query('perPage', 10);
            $perPage     = max(1, min($perPage, 100));

            $query = $this->saldoCuti->newQuery();

            if ($searchJenis || $searchJumlah) {
                $query->where(function ($query) use ($searchJenis, $searchJumlah) {
                    if ($searchJenis) {
                        $query->where('jenis', 'like', '%' . $searchJenis . '%');
                    }
                    if ($searchJumlah) {
                        $query->where('jumlah', $searchJumlah);
                    }
                });
            }

            $saldoCutis = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
            return $this->successResponse($saldoCutis, 'Data Saldo Cuti berhasil diambil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function jabatanOptions(Request $request)
    {
        try {

            $term    = trim((string) $request->get('q', $request->get('term', '')));
            $page    = max(1, (int) $request->get('page', 1));
            $perPage = (int) $request->get('perPage', 20);
            $perPage = max(1, min($perPage, 50));

            $q = Jabatan::query()
                ->select('id', 'name');

            if ($term !== '') {
                $q->where('name', 'like', '%' . $term . '%');
            }

            $paginator = $q->orderBy('name')
                ->paginate($perPage, ['*'], 'page', $page);

            $results = $paginator->getCollection()->map(fn ($c) => [
                'id'   => $c->id,
                'text' => $c->name,
            ])->values();

            $data = [
                'results' => $results,
                'pagination' => [
                    'more' => $paginator->hasMorePages(),
                ],
            ];

            return $this->successResponse($data, 'Data Saldo Cuti berhasil diambil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function store(SaldoCutiStoreFormRequest $request)
    {
        try {

            $data = $request->validated();
            $data['created_by'] = JwtHelper::getClaim($request, 'name');

            $saldoCuti = $this->saldoCuti->create($data);

            return $this->successResponse($saldoCuti, 'Saldo Cuti berhasil dibuat', 201);

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
            $saldoCuti = $this->saldoCuti->findOrFail($id);
            return $this->successResponse($saldoCuti, 'Data Saldo Cuti berhasil diambil', 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function update(SaldoCutiUpdateFormRequest $request, $id)
    {
        try {

            $saldoCuti = $this->saldoCuti->findOrFail($id);
            $data = $request->validated();
            $data['updated_by'] = JwtHelper::getClaim($request, 'name');

            $saldoCuti->update($data);

            return $this->successResponse($saldoCuti, 'Saldo Cuti berhasil diperbarui', 200);

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
            $saldoCuti = $this->saldoCuti->findOrFail($id);
            $saldoCuti->delete();
            return $this->successResponse(null, 'Saldo Cuti berhasil dihapus', 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }
}
