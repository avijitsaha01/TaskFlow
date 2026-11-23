<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo account: demo / demo@taskflow.test / password
        User::updateOrCreate(
            ['email' => 'demo@taskflow.test'],
            [
                'name' => 'demo',
                'username' => 'demo',
                'phone' => '+15550000001',
                'password' => 'password',
            ]
        );

        $this->call([
            TaskSeeder::class,
        ]);
    }
}
