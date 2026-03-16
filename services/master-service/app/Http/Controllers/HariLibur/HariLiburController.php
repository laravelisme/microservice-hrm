<?php

namespace App\Http\Controllers\HariLibur;

use App\Events\HariLiburCreated;
use App\Events\HariLiburUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\HariLibur\HariLiburStoreFormRequest;
use App\Http\Requests\HariLibur\HariLiburUpdateFormRequest;
use App\Models\MasterData\Company;
use App\Models\MasterData\HariLibur;
use App\Models\MasterData\HariLiburCompany;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HariLiburController extends Controller
{
    private HariLibur $hariLibur;

    public function __construct(HariLibur $hariLibur)
    {
        $this->hariLibur = $hariLibur;
    }

    public function index(Request $request)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {

            $searchName = trim((string) $request->query('searchName', ''));
            $searchTahun = trim((string) $request->query('searchTahun', ''));
            $searchIsBersama = trim((string) $request->query('searchIsBersama', ''));
            $searchIsUmum = trim((string) $request->query('searchIsUmum', ''));
            $perPage    = (int) $request->query('perPage', 10);
            $perPage    = max(1, min($perPage, 100));

            $query = $this->hariLibur->newQuery();

            if ($searchName || $searchTahun || $searchIsBersama || $searchIsUmum) {
                $query->where(function ($query) use ($searchName, $searchTahun, $searchIsBersama, $searchIsUmum) {
                    if ($searchName) {
                        $query->where('hari_libur', 'like', '%' . $searchName . '%');
                    }
                    if ($searchTahun) {
                        $query->whereYear('tanggal_mulai', $searchTahun);
                    }
                    if ($searchIsBersama) {
                        $query->where('is_cuti_bersama', $searchIsBersama);
                    }
                    if ($searchIsUmum) {
                        $query->where('is_umum', $searchIsUmum);
                    }
                });
            }

            $hariLiburs = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
            return $this->successResponse($hariLiburs, 'Data hari libur berhasil diambil', 200);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[JabatanController@show] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

    public function companyOptions(Request $request)
    {
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

            return $this->successResponse($data, 'Data hari libur berhasil diambil', 200);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[HariLiburController@companyOptions] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

    public function store(HariLiburStoreFormRequest $request)
    {
        try {

            $data = $request->validated();

            $hariLibur = null;

            DB::transaction(function () use ($data, &$hariLibur) {

                $hariLibur = $this->hariLibur->create([
                    'hari_libur'        => $data['hari_libur'],
                    'tanggal_mulai'     => $data['tanggal_mulai'],
                    'tanggal_selesai'   => $data['tanggal_selesai'],
                    'is_cuti_bersama'   => (int) $data['is_cuti_bersama'],
                    'is_umum'           => (int) $data['is_umum'],
                    'is_repeat'         => (int) $data['is_repeat'],
                ]);

                $isUmum = ((int) $data['is_umum']) === 1;
                $companyIds = $isUmum ? [] : ($data['company_ids'] ?? []);

                DB::afterCommit(function () use ($hariLibur, $isUmum, $companyIds) {
                    event(new HariLiburCreated(
                        hariLiburId: (int) $hariLibur->id,
                        isUmum: $isUmum,
                        companyIds: $companyIds
                    ));
                });
            });

            return $this->successResponse($hariLibur, 'Hari libur data stored successfully', 201);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[HariLiburController@store] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

    public function show(Request $request, $id)
    {

        if (!JwtHelper::hasRole($request, 'super-admin')) {
            return response()->json(['message' => 'Role anda tidak diperbolehkan'], 403);
        }

        try {

            $hariLibur = $this->hariLibur->findOrFail($id);

            $selectedCompanyIds = HariLiburCompany::where('hari_libur_id', $hariLibur->id)
                ->pluck('company_id')
                ->toArray();

            $selectedCompanies = Company::query()
                ->select('id', 'company_name')
                ->whereIn('id', $selectedCompanyIds)
                ->orderBy('company_name')
                ->get();

            return $this->successResponse([
                'hariLibur' => $hariLibur,
                'companies' => [
                    'selected' => $selectedCompanies,
                    'selectedIds' => $selectedCompanyIds,
                    ]
                ], 'Data hari libur berhasil diambil', 200);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[HariLiburController@show] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

    public function update(HariLiburUpdateFormRequest $request, $id)
    {
        try {

            $data = $request->validated();

            $companyIds = $data['company_ids'] ?? [];
            unset($data['company_ids']);

            $hariLibur = null;

            DB::transaction(function () use ($id, $data, $companyIds, &$hariLibur) {
                $hariLibur = $this->hariLibur->findOrFail($id);

                $hariLibur->update([
                    'hari_libur'        => $data['hari_libur'],
                    'tanggal_mulai'     => $data['tanggal_mulai'],
                    'tanggal_selesai'   => $data['tanggal_selesai'],
                    'is_cuti_bersama'   => (int) $data['is_cuti_bersama'],
                    'is_umum'           => (int) $data['is_umum'],
                    'is_repeat'         => (int) $data['is_repeat'],
                ]);

                $isUmum = ((int) $hariLibur->is_umum) === 1;
                $ids = $isUmum ? [] : $companyIds;

                DB::afterCommit(function () use ($hariLibur, $isUmum, $ids) {
                    event(new HariLiburUpdated(
                        hariLiburId: (int) $hariLibur->id,
                        isUmum: $isUmum,
                        companyIds: $ids
                    ));
                });
            });

            return $this->successResponse($hariLibur->fresh(), 'Hari libur data updated successfully', 200);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[HariLiburController@update] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $hariLibur = $this->hariLibur->findOrFail($id);
            $hariLibur->delete();

            return $this->successResponse($hariLibur, 'Hari libur data deleted successfully', 200);

        } catch (\Throwable $e) {
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            Log::error('[HariLiburController@destroy] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse('Terjadi kesalahan di server', 500);
        }
    }

}
