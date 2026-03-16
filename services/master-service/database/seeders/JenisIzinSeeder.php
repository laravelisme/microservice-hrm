<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterData\JenisIzin;

class JenisIzinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'PULANG LEBIH CEPAT', 'kode' => 'EARLYOUT'],
            ['name' => 'DATANG TERLAMBAT', 'kode' => 'LATEIN'],
            ['name' => 'SAKIT', 'kode' => 'SICK'],
            ['name' => 'TIDAK MASUK KERJA', 'kode' => 'ABSENCE'],
        ];

        foreach ($items as $item) {
            JenisIzin::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
