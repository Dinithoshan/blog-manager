<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds all roles and permissions for the database.
     */
    public function run(): void
    {
        //Defining Permissions for all the roles
        $writer_permissions = ["write-article", "edit-unpublished-article", "delete-unpublised-article", "view-article"];
        $manager_permissions = ["publish", "unpublish"];
        $admin_permissions =  ["manage-users","publish", "unpublish","write-article", "edit-unpublished-article", "delete-unpublised-article", "view-article"];

        // Creating Writer role and assigning blog crud permissions.
        $role = Role::create(['name' => 'writer']);
        foreach ($writer_permissions as $permission) {
            $permission = Permission::create(['name' => $permission]);
            $role->givePermissionTo($permission);
        }

        // Creating Manager role and assigning publish/unpublish permissions.
        $role = Role::create(['name' => 'manager']);
        foreach ($manager_permissions as $permission) {
            $permission = Permission::create(['name' => $permission]);
            $role->givePermissionTo($permission);
        }

        // Creating admin role and assigning all rights as permissions
        $role = Role::create(['name' => 'admin']);
        foreach ($admin_permissions as $permission) {
            //Escaping already set permissions to prevent them from duplicate seeding
            if (!Permission::where('name', $permission)->exists()) {
                $permission = Permission::create(['name' => $permission]);
            }
            $role->givePermissionTo($permission);
         }
    }

}
