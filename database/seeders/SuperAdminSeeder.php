<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $uuid = Str::uuid()->toString();
        $admin = User::factory()->create([
            "name" => "useruser3",
            'email' => "useruser3@gmail.com",
            "password" => bcrypt("shqiptare"),
            'public_id' => $uuid
            
        ]);
        $admin->assignRole('super-admin');
    }
}
