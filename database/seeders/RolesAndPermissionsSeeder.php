<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on SRS Section 3.1.5
        $permissions = [
            // Student management permissions
            'student.view',
            'student.create',
            'student.update',
            'student.delete',

            // Enrollment management permissions
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            'enrollment.approve',
            'enrollment.reject',

            // Document management permissions
            'documents.view',
            'documents.verify',

            // Report permissions
            'reports.view',
            'reports.generate',

            // User management permissions
            'users.manage',

            // System configuration permissions
            'system.configure',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions based on SRS Section 3.1.3

        // Super Admin - Complete system access
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Administrator - Full system access, user management, system configuration
        $administrator = Role::create(['name' => 'administrator']);
        $administrator->givePermissionTo(Permission::all());

        // Registrar - Enrollment processing, student records management, reporting
        $registrar = Role::create(['name' => 'registrar']);
        $registrar->givePermissionTo([
            'student.view',
            'student.create',
            'student.update',
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            'enrollment.approve',
            'enrollment.reject',
            'documents.view',
            'documents.verify',
            'reports.view',
            'reports.generate',
        ]);

        // Parent/Guardian - Enrollment form submission, status tracking, billing access
        $parent = Role::create(['name' => 'parent']);
        $parent->givePermissionTo([
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            'documents.view',
        ]);

        // Student - Limited access to own enrollment status and information
        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'enrollment.view',
        ]);
    }
}
