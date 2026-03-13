<?php

namespace App\Http\Controllers\HariLibur;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Company;
use App\Models\MasterData\HariLibur;
use App\Models\MCompany;
use App\Services\JwtHelper;
use Illuminate\Http\Request;
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

}
