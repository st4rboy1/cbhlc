# Ticket #008: Permission Management UI

## Priority: Medium (Could Have)

## Related SRS Requirements

- **Section 3.1.3:** User Roles (Spatie Laravel Permission Implementation)
- **Section 3.1.4:** Permission Structure
- **Section 3.1.5:** Core Permissions
- **NFR-2.3:** RBAC implementation using Spatie Laravel Permission package

## Current Status

⚠️ **PARTIALLY IMPLEMENTED**

Current implementation:

- Spatie Laravel Permission package is installed
- Permission tables exist in database
- Backend permission system functional
- **Missing:** User-friendly UI for managing roles and permissions

## Required Implementation

### 1. Backend Layer

**Controllers:**

- `SuperAdmin/RoleController.php` - Manage roles
- `SuperAdmin/PermissionController.php` - Manage permissions
- `SuperAdmin/UserRoleController.php` - Assign roles to users

**Routes:**

```php
// Super Admin routes
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Roles Management
    Route::resource('roles', RoleController::class);
    Route::post('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');
    Route::post('/roles/{role}/duplicate', [RoleController::class, 'duplicate'])->name('roles.duplicate');

    // Permissions Management
    Route::resource('permissions', PermissionController::class);
    Route::post('/permissions/sync', [PermissionController::class, 'sync'])->name('permissions.sync');

    // User Role Assignment
    Route::post('/users/{user}/roles', [UserRoleController::class, 'assignRole'])->name('users.assign-role');
    Route::delete('/users/{user}/roles/{role}', [UserRoleController::class, 'removeRole'])->name('users.remove-role');
    Route::post('/users/{user}/permissions', [UserRoleController::class, 'givePermission'])->name('users.give-permission');
    Route::delete('/users/{user}/permissions/{permission}', [UserRoleController::class, 'revokePermission'])->name('users.revoke-permission');
});
```

### 2. Frontend Layer

**Pages:**

#### a) Roles Management

`/resources/js/pages/super-admin/roles/index.tsx`

- List all roles
- Create new role button
- Edit/delete actions
- User count per role
- Permission count per role

`/resources/js/pages/super-admin/roles/create.tsx`

- Role name and guard name
- Select permissions (grouped by category)
- Description field

`/resources/js/pages/super-admin/roles/edit.tsx`

- Update role details
- Manage permissions
- View assigned users

`/resources/js/pages/super-admin/roles/show.tsx`

- Role details
- List of permissions
- List of users with this role
- Audit log of role changes

#### b) Permissions Management

`/resources/js/pages/super-admin/permissions/index.tsx`

- List all permissions
- Grouped by entity (students, enrollments, etc.)
- Create new permission
- Edit/delete actions

`/resources/js/pages/super-admin/permissions/create.tsx`

- Permission name
- Guard name
- Description
- Category/group

#### c) User Permission Management

Enhance existing user edit page:
`/resources/js/pages/super-admin/users/edit.tsx`

- Assign roles section
- Direct permissions section
- Permission inheritance display
- Role switcher

**Components:**

#### RoleCard

```tsx
// Display role with key information
interface RoleCardProps {
    role: Role;
    onEdit: () => void;
    onDelete: () => void;
    onDuplicate: () => void;
}
```

#### PermissionCheckboxGroup

```tsx
// Grouped permission checkboxes
interface PermissionCheckboxGroupProps {
    permissions: Permission[];
    selected: number[];
    onChange: (permissions: number[]) => void;
    grouped?: boolean; // Group by entity
}
```

#### RoleAssignmentDialog

```tsx
// Modal for assigning roles to user
interface RoleAssignmentDialogProps {
    user: User;
    availableRoles: Role[];
    currentRoles: Role[];
    onAssign: (roleId: number) => void;
}
```

#### PermissionMatrix

```tsx
// Visual permission matrix (roles × permissions)
interface PermissionMatrixProps {
    roles: Role[];
    permissions: Permission[];
    onToggle: (roleId: number, permissionId: number) => void;
}
```

### 3. Features

#### a) Role Management

- **Create Role:**
    - Name, description, guard name
    - Select permissions from grouped list
    - Permission search and filter

- **Edit Role:**
    - Update basic info
    - Add/remove permissions
    - Bulk permission assignment

- **Delete Role:**
    - Prevent deletion if users assigned
    - Option to reassign users before deletion

- **Duplicate Role:**
    - Clone role with all permissions
    - Useful for creating similar roles

- **Role Details:**
    - View all permissions
    - View all users
    - Recent changes log

#### b) Permission Management

- **List Permissions:**
    - Grouped by entity (students, enrollments, etc.)
    - Show which roles have each permission
    - Search and filter

- **Create Permission:**
    - Permission name (follow naming convention)
    - Description
    - Category/group
    - Guard name

- **Edit Permission:**
    - Update description
    - Cannot change name (would break code)

- **Delete Permission:**
    - Prevent deletion if assigned to roles
    - Show affected roles

- **Sync Permissions:**
    - Generate permissions from code
    - Auto-detect new permissions
    - Mark unused permissions

#### c) User Role Assignment

- **Assign Role:**
    - Select role from dropdown
    - Assign button with confirmation
    - Show inherited permissions

- **Remove Role:**
    - Remove with confirmation
    - Show permission changes

- **Direct Permissions:**
    - Give specific permission to user
    - Bypass role system
    - Show in different color

- **Permission Overview:**
    - Show all permissions (role + direct)
    - Indicate source (which role)
    - Visual hierarchy

#### d) Permission Matrix View

Visual table showing:

- Rows: Roles
- Columns: Permissions (grouped by entity)
- Cells: Checkboxes to toggle
- Quick bulk assignment

### 4. Permission Categories

**Organize permissions by entity:**

- **Students:** student.view, student.create, student.update, student.delete
- **Enrollments:** enrollment.view, enrollment.create, enrollment.update, enrollment.approve, enrollment.reject
- **Documents:** documents.view, documents.verify, documents.delete
- **Reports:** reports.view, reports.generate, reports.export
- **Users:** users.view, users.create, users.update, users.delete, users.manage
- **System:** system.configure, system.settings, audit-logs.view
- **Billing:** billing.view, billing.create, billing.update, invoices.view, payments.view

### 5. Default Roles Setup

**Create Seeder:**

```php
// database/seeders/RolePermissionSeeder.php
class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Super Admin - all permissions
        $superAdmin = Role::create(['name' => 'super_admin']);

        // Administrator
        $admin = Role::create(['name' => 'administrator']);
        $admin->givePermissionTo([
            'student.view', 'student.create', 'student.update',
            'enrollment.view', 'enrollment.update',
            'users.view', 'users.create', 'users.update',
        ]);

        // Registrar
        $registrar = Role::create(['name' => 'registrar']);
        $registrar->givePermissionTo([
            'student.view', 'student.create', 'student.update',
            'enrollment.view', 'enrollment.approve', 'enrollment.reject',
            'documents.view', 'documents.verify',
            'reports.view', 'reports.generate',
        ]);

        // Guardian
        $guardian = Role::create(['name' => 'guardian']);
        $guardian->givePermissionTo([
            'enrollment.create', 'enrollment.view',
            'student.view', 'student.update',
            'billing.view',
        ]);

        // Student
        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'enrollment.view',
        ]);
    }
}
```

### 6. Authorization in Code

**Gate Definitions:**

```php
// In AuthServiceProvider
Gate::before(function (User $user, string $ability) {
    return $user->hasRole('super_admin') ? true : null;
});
```

**Middleware Usage:**

```php
// In routes
Route::middleware(['role:super_admin'])->group(function () {
    // Routes
});

Route::middleware(['permission:enrollment.approve'])->group(function () {
    // Routes
});
```

**Blade Directives:**

```blade
@role('super_admin')
    <!-- Content -->
@endrole

@can('enrollment.approve')
    <!-- Content -->
@endcan
```

**React/Inertia:**

```tsx
{
    can('enrollment.approve') && <Button>Approve</Button>;
}

{
    hasRole('super_admin') && <AdminPanel />;
}
```

### 7. Validation and Business Rules

**Constraints:**

- Super Admin role cannot be deleted
- Super Admin role cannot have permissions removed
- At least one user must have Super Admin role
- Role names must be unique
- Permission names must follow convention: `entity.action`
- Cannot remove role if users are assigned (option to reassign)
- Cannot remove permission if roles have it (unless force flag)

**Validation Rules:**

```php
// CreateRoleRequest
public function rules()
{
    return [
        'name' => 'required|string|max:125|unique:roles,name',
        'guard_name' => 'required|string|max:125',
        'permissions' => 'array',
        'permissions.*' => 'exists:permissions,id',
    ];
}
```

## Acceptance Criteria

✅ Super Admin can view all roles and permissions
✅ Super Admin can create new roles with permissions
✅ Super Admin can edit existing roles
✅ Super Admin can delete roles (with constraints)
✅ Super Admin can create new permissions
✅ Super Admin can assign roles to users
✅ Super Admin can give direct permissions to users
✅ Permission matrix view works correctly
✅ Permission inheritance is clear and visible
✅ UI is intuitive and user-friendly
✅ Changes are logged in audit log
✅ Proper validation and error handling
✅ Cannot break Super Admin role

## Testing Requirements

- Feature tests for role CRUD
- Feature tests for permission CRUD
- Feature tests for role assignment
- Authorization tests
- UI tests for permission matrix
- Validation tests
- Business rule tests (constraints)

## Estimated Effort

**Medium Priority:** 3-4 days

## Dependencies

- Spatie Laravel Permission package (already installed)
- Audit logging system
- Proper Super Admin access

## Implementation Phases

**Phase 1: Backend (1 day)**

- Controllers for roles and permissions
- Routes and validation
- Business logic

**Phase 2: Frontend - Roles (1 day)**

- Role list page
- Create/edit role pages
- Role assignment dialog

**Phase 3: Frontend - Permissions (1 day)**

- Permission list page
- Permission matrix view
- User permission overview

**Phase 4: Testing and Polish (0.5-1 day)**

- Comprehensive testing
- UI/UX improvements
- Documentation

## Notes

- Consider adding role templates for quick setup
- Add permission descriptions in UI
- Consider permission groups/categories
- Add role comparison feature
- Consider permission analytics (most used, etc.)
- Add role migration tool for deployment
- Document permission naming convention
- Consider API for external permission management
