<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterData\JenisCuti;

class JenisCutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'TAHUNAN', 'kode' => 'TAHUNAN'],
            ['name' => 'MENIKAH', 'kode' => 'MENIKAH'],
            ['name' => 'MELAHIRKAN', 'kode' => 'MELAHIRKAN'],
            ['name' => 'POTONG CUTI', 'kode' => 'POTONG_CUTI'],
        ];

        foreach ($items as $item) {
            JenisCuti::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
