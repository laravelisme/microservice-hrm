<?php

namespace App\Http\Controllers\SaldoCuti;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaldoCuti\SaldoCutiStoreFormRequest;
use App\Http\Requests\SaldoCuti\SaldoCutiUpdateFormRequest;
use App\Models\MasterData\Jabatan;
use App\Models\MasterData\JenisCuti;
use App\Models\MasterData\SaldoCuti;
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

            $query = $this->saldoCuti->newQuery()->with('jenisCuti');

            if ($searchJenis || $searchJumlah) {
                $query->where(function ($query) use ($searchJenis, $searchJumlah) {
                    if ($searchJenis) {
                        // allow searching by jenis_cuti name or the old 'jenis' string
                        $query->whereHas('jenisCuti', fn($q) => $q->where('name', 'like', '%' . $searchJenis . '%'));
                    }
                    if ($searchJumlah) {
                        $query->where('jumlah', $searchJumlah);
                    }
                });
            }

            $paginator = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

            $collection = $paginator->getCollection();

            $jabatanIds = $collection->pluck('jabatan_id')->filter()->unique()->values()->all();
            $jabatanMap = [];
            if (!empty($jabatanIds)) {
                $jabatanMap = Jabatan::whereIn('id', $jabatanIds)->pluck('name', 'id')->toArray();
            }

            $jenisIds = $collection->pluck('jenis_cuti_id')->filter()->unique()->values()->all();
            $jenisMap = [];
            if (!empty($jenisIds)) {
                $jenisMap = JenisCuti::whereIn('id', $jenisIds)->pluck('name', 'id')->toArray();
            }

            $newCollection = $collection->map(function ($item) use ($jabatanMap, $jenisMap) {
                $data = is_array($item) ? $item : $item->toArray();
                $jabatanId = $data['jabatan_id'] ?? null;
                $data['jabatan_name'] = $jabatanId ? ($jabatanMap[$jabatanId] ?? null) : null;
                $jenisId = $data['jenis_cuti_id'] ?? null;
                $data['jenis_cuti_name'] = $jenisId ? ($jenisMap[$jenisId] ?? null) : null;
                return $data;
            });
            $paginator->setCollection($newCollection);

            return $this->successResponse($paginator, 'Data Saldo Cuti berhasil diambil', 200);

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

    public function jenisCutiOptions(Request $request)
    {
        try {

            $term    = trim((string) $request->get('q', $request->get('term', '')));
            $page    = max(1, (int) $request->get('page', 1));
            $perPage = (int) $request->get('perPage', 20);
            $perPage = max(1, min($perPage, 50));

            $q = JenisCuti::query()
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

            return $this->successResponse($data, 'Data Jenis Cuti berhasil diambil', 200);

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
            $jenisCuti = JenisCuti::find($data['jenis']);
            if (!$jenisCuti) {
                return $this->errorResponse('Jenis Cuti tidak ditemukan', 404);
            }
            $data['jenis'] = $jenisCuti->name;
            $data['jenis_cuti_id'] = $jenisCuti->id;
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
            $saldoCuti = $this->saldoCuti->with('jenisCuti')->findOrFail($id);
            $jabatan = Jabatan::find($saldoCuti->jabatan_id);
            $saldoCuti = $saldoCuti->toArray();
            $saldoCuti['jabatan_name'] = $jabatan->name ?? null;
            $saldoCuti['jenis_cuti_name'] = $saldoCuti['jenis_cuti']? $saldoCuti['jenis_cuti']['name'] : null;
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
            $jenisCuti = JenisCuti::find($data['jenis']);
            if (!$jenisCuti) {
                return $this->errorResponse('Jenis Cuti tidak ditemukan', 404);
            }

            $data['jenis'] = $jenisCuti->name;
            $data['jenis_cuti_id'] = $jenisCuti->id;

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
