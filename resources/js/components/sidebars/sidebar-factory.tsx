import { type User } from '@/types';
import { AdminSidebar } from './admin-sidebar';
import { GuardianSidebar } from './guardian-sidebar';
import { RegistrarSidebar } from './registrar-sidebar';
import { StudentSidebar } from './student-sidebar';
import { SuperAdminSidebar } from './super-admin-sidebar';

/**
 * Factory function to return the appropriate sidebar based on user role.
 * Implements the Strategy pattern for SOLID compliance.
 */
export function getSidebarForRole(user: User | null) {
    if (!user || !user.roles || user.roles.length === 0) {
        return null;
    }

    // Get the primary role (first role in the array)
    const primaryRole = user.roles[0].name;

    switch (primaryRole) {
        case 'super_admin':
            return <SuperAdminSidebar />;
        case 'administrator':
            return <AdminSidebar />;
        case 'registrar':
            return <RegistrarSidebar />;
        case 'guardian':
            return <GuardianSidebar />;
        case 'student':
            return <StudentSidebar />;
        default:
            // Fallback to a basic sidebar if role is not recognized
            return <StudentSidebar />;
    }
}
