<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::create([
//            'name' => 'Test User',
//            'username' => '3505160106040001',
//            'password' => Hash::make('040001'),
//            'email' => 'test@example.com',
//        ]);

//        Role::firstOrCreate(['name' => 'super-admin']);
        Role    ::firstOrCreate(['name' => 'normal']);

        $user = User::find(1);

        if ($user) {
//            $user->assignRole('super-admin');
            $user->assignRole('normal');
        }


    }
}
