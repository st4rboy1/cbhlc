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

            // Grade level fees management permissions
            'grade_level_fees.manage',

            // Billing management permissions
            'billing.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions based on SRS Section 3.1.3

        // Super Admin - Complete system access
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Administrator - Full system access, user management, system configuration
        $administrator = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $administrator->syncPermissions(Permission::all());

        // Registrar - Enrollment processing, student records management, reporting, fees management
        $registrar = Role::firstOrCreate(['name' => 'registrar', 'guard_name' => 'web']);
        $registrar->syncPermissions([
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
            'grade_level_fees.manage',
        ]);

        // Guardian - Enrollment form submission, status tracking, billing access
        $guardian = Role::firstOrCreate(['name' => 'guardian', 'guard_name' => 'web']);
        $guardian->syncPermissions([
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            'documents.view',
            'billing.view',
        ]);

        // Student - Limited access to own enrollment status and information
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'enrollment.view',
        ]);
    }
}
