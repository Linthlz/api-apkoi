<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //Permissions
        $permissions = [
            //UNIV UPTS
            'manage news',
            'manage kurikulum',
            'manage profile',
            'manage certificate',
            'manage jurnal',
            'manage agenda',

            //SuperAdmin
            'manage users',
            'manage roles',
            'manage permission',
            'see user',
        ];
        foreach($permissions as $permission){
            Permission::firstOrCreate(['name'=> $permission]);
        }

        //Create SuperAdmin Role
        $superAdmin = Role::firstOrCreate(['name'=>'super-admin']);
        $superAdmin->givePermissionTo([
            'manage users',
            'manage roles',
            'manage permission',
            'see user',
        ]);
        //Create user role
        $user = Role::firstOrCreate(['name'=>'univ-upt']);
        $user->givePermissionTo([
            'manage news',
            'manage kurikulum',
            'manage profile',
            'manage certificate',
            'manage jurnal',
            'manage agenda',
        ]);

    }
    
}
