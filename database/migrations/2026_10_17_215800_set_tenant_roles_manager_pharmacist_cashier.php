<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Check if 'Manager' role exists, if not rename 'Admin' to 'Manager' or insert 'Manager'
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        $managerRole = DB::table('roles')->where('name', 'Manager')->first();

        if ($adminRole && !$managerRole) {
            DB::table('roles')->where('id', $adminRole->id)->update([
                'name' => 'Manager',
                'updated_at' => now(),
            ]);
            $managerRoleId = $adminRole->id;
        } elseif (!$managerRole) {
            $managerRoleId = DB::table('roles')->insertGetId([
                'name' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $managerRoleId = $managerRole->id;
        }

        // If 'Admin' role still exists separately, migrate users to 'Manager'
        if ($adminRole && $managerRole && $adminRole->id !== $managerRole->id) {
            DB::table('users')->where('role_id', $adminRole->id)->update([
                'role_id' => $managerRoleId,
            ]);
            DB::table('roles')->where('id', $adminRole->id)->delete();
        }

        // Ensure Pharmacist and Cashier exist
        if (!DB::table('roles')->where('name', 'Pharmacist')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Pharmacist',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('roles')->where('name', 'Cashier')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('roles')->where('name', 'Super Admin')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Super Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Reversible if needed
        $managerRole = DB::table('roles')->where('name', 'Manager')->first();
        if ($managerRole && !DB::table('roles')->where('name', 'Admin')->exists()) {
            DB::table('roles')->where('id', $managerRole->id)->update([
                'name' => 'Admin',
                'updated_at' => now(),
            ]);
        }
    }
};
