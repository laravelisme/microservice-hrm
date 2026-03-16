<?php

namespace App\Listeners;

use App\Events\HariLiburCreated;
use App\Models\MasterData\Company;
use App\Models\MasterData\HariLiburCompany;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttachCompaniesToHariLibur implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(HariLiburCreated $event): void
    {
        $now = now();

        if ($event->isUmum) {
            Company::query()
                ->select('id')
                ->orderBy('id')
                ->chunkById(1000, function ($companies) use ($event, $now) {
                    $rows = [];

                    foreach ($companies as $c) {
                        $rows[] = [
                            'hari_libur_id' => $event->hariLiburId,
                            'company_id'    => $c->id,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];
                    }

                    HariLiburCompany::insert($rows);
                });

            return;
        }

        $ids = array_values(array_unique(array_filter($event->companyIds)));
        if (empty($ids)) return;

        foreach (array_chunk($ids, 1000) as $chunk) {
            $rows = [];
            foreach ($chunk as $companyId) {
                $rows[] = [
                    'hari_libur_id' => $event->hariLiburId,
                    'company_id'    => (int) $companyId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            HariLiburCompany::insert($rows);
        }
    }
}
