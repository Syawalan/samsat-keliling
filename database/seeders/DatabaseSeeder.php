<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        if ($email && $password) {
            User::query()->updateOrCreate(['email' => $email], [
                'name' => env('ADMIN_NAME', 'Administrator Samsat'),
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);
        }

        $this->call([LandingSectionSeeder::class, DemoDataSeeder::class]);
    }
}