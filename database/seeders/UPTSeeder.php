<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UPTSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uuid = Str::uuid()->toString();
        $admin = User::factory()->create([
            "name" => "UPT Apkoi Undiksha",
            'email' => "UPTUndiksha@apkoi.web.id",
            "password" => bcrypt("ballikombetar1944"),
            'public_id' => $uuid
            
        ]);
        $admin->assignRole('univ-upt');
    }
}
