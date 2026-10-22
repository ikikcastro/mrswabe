<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // roles must exist before...
            OwnerSeeder::class,               // ...this assigns one to Mark
            RoomSeeder::class,                // demo rooms for /rooms to list
        ]);
    }
}
