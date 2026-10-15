<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Izin baru untuk mengelola agenda, diberikan ke role univ-upt
     * (role yang sama dengan pengelola berita, sertifikat, kurikulum, dan jurnal).
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'manage agenda', 'guard_name' => 'web']);

        $role = Role::where('name', 'univ-upt')->first();
        if ($role) {
            $role->givePermissionTo($permission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::where('name', 'manage agenda')->delete();
    }
};
