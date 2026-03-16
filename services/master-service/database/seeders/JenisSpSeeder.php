<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterData\JenisSp;

class JenisSpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Surat Peringatan 1', 'kode' => 'SP1'],
            ['name' => 'Surat Perinagat 2', 'kode' => 'SP2'],
            ['name' => 'Surat Peringatan 3', 'kode' => 'SP3'],
        ];

        foreach ($items as $item) {
            JenisSp::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
