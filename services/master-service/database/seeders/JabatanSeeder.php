<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterData\Jabatan;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Staff', 'kode' => 'STAFF', 'level' => 1],
            ['name' => 'Supervisor', 'kode' => 'SUPERVISOR', 'level' => 2],
            ['name' => 'Manager', 'kode' => 'MANAGER', 'level' => 3],
        ];

        foreach ($items as $item) {
            Jabatan::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
