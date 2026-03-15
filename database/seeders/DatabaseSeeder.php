<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Planes de subscripción
        $this->call(PlansSeeder::class);

        // Usuario administrador inicial
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@verificador.com.ar')],
            [
                'name'     => 'Admin',
                'email'    => env('ADMIN_EMAIL', 'admin@verificador.com.ar'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme')),
                'role'     => 'admin',
                'store_id' => null,
            ]
        );
    }
}
