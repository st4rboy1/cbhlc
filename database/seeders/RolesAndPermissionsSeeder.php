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

            // Guardian management permissions
            'guardian.view',
            'guardian.create',
            'guardian.update',
            'guardian.delete',

            // Enrollment management permissions
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            'enrollment.approve',
            'enrollment.reject',

            // Document management permissions
            'documents.view',
            'documents.verify',
            'documents.upload',
            'documents.download',
            'documents.delete',

            // Invoice management permissions
            'invoice.view',
            'invoice.create',
            'invoice.update',
            'invoice.delete',
            'invoice.download',

            // Payment management permissions
            'payment.view',
            'payment.create',
            'payment.update',
            'payment.delete',
            'payment.record',

            // Receipt management permissions
            'receipt.view',
            'receipt.generate',
            'receipt.download',
            'receipt.print',

            // School year management permissions
            'school_year.view',
            'school_year.create',
            'school_year.update',
            'school_year.delete',
            'school_year.activate',

            // Enrollment period management permissions
            'enrollment_period.view',
            'enrollment_period.create',
            'enrollment_period.update',
            'enrollment_period.delete',
            'enrollment_period.activate',
            'enrollment_period.close',

            // Grade level fees management permissions (CRUD pattern)
            'grade_level_fees.view',
            'grade_level_fees.create',
            'grade_level_fees.update',
            'grade_level_fees.delete',

            // Report permissions
            'reports.view',
            'reports.generate',

            // User management permissions
            'users.manage',

            // System configuration permissions
            'system.configure',
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
            // Student management
            'student.view',
            'student.create',
            'student.update',
            // Guardian management
            'guardian.view',
            'guardian.create',
            'guardian.update',
            // Enrollment management
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            'enrollment.approve',
            'enrollment.reject',
            // Document management
            'documents.view',
            'documents.verify',
            'documents.upload',
            'documents.download',
            // Invoice management
            'invoice.view',
            'invoice.create',
            'invoice.update',
            'invoice.download',
            // Payment management
            'payment.view',
            'payment.create',
            'payment.record',
            // Receipt management
            'receipt.view',
            'receipt.generate',
            'receipt.download',
            // School year management
            'school_year.view',
            // Enrollment period management
            'enrollment_period.view',
            // Grade level fees management
            'grade_level_fees.view',
            'grade_level_fees.create',
            'grade_level_fees.update',
            // Reporting
            'reports.view',
            'reports.generate',
        ]);

        // Guardian - Enrollment form submission, status tracking, billing access
        // Note: Most guardian permissions require policy checks to ensure they only access their own data
        $guardian = Role::firstOrCreate(['name' => 'guardian', 'guard_name' => 'web']);
        $guardian->syncPermissions([
            // Enrollment management (own children only)
            'enrollment.view',
            'enrollment.create',
            'enrollment.update',
            // Document management (own children only)
            'documents.view',
            'documents.upload',
            'documents.download',
            // Invoice and payment (own only)
            'invoice.view',
            'invoice.download',
            'payment.view',
            // Receipt viewing (own only)
            'receipt.view',
            'receipt.download',
            // Guardian profile management (own only)
            'guardian.update',
        ]);

        // Student - Limited access to own enrollment status and information
        // Note: Student permissions require policy checks to ensure they only access their own data
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            // Own enrollment info only
            'enrollment.view',
            // Own invoices and receipts only
            'invoice.view',
            'receipt.view',
        ]);
    }
}
