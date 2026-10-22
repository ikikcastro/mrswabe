<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'mark'],
            [
                'name' => 'Mark',
                'email' => 'mark@ikikhouse.test',
                'password' => Hash::make('tahimiklang'),
            ],
        )->assignRole('owner');
    }
}
