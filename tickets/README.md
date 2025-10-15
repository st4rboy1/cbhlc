# CBHLC Enrollment System - Tickets & Epics

This directory contains epics and implementable story tickets for features identified by comparing the current implementation against the SRS.

## Structure

- **EPIC-XXX-*.md**: High-level feature requirements with full context
- **TICKET-XXX-*.md**: Implementable stories (0.5-2 days each)

## Priority & Status

### Critical Priority (Must Have) ✅

#### EPIC-001: Document Management System
**Status:** In Progress (4/6 complete) | **Total Effort:** 5.5 days | **Completed:** 4 days

| Ticket | Title | Effort | Status | Dependencies |
|--------|-------|--------|--------|--------------|
| [#001](./TICKET-001-create-document-model-migration.md) | Create Document Model and Migration | 0.5 day | ✅ COMPLETED | None |
| [#002](./TICKET-002-implement-document-upload-backend.md) | Implement Document Upload Backend | 1 day | ✅ COMPLETED | #001 |
| [#003](./TICKET-003-build-document-upload-ui.md) | Build Document Upload UI Component | 1 day | ✅ COMPLETED | #002 |
| [#004](./TICKET-004-document-list-and-management.md) | Document List and Management UI | 1 day | ✅ COMPLETED | #002, #003 |
| [#005](./TICKET-005-document-verification-workflow.md) | Document Verification Workflow | 1.5 days | ❌ Not Started | #001, #004 |
| [#006](./TICKET-006-document-security-validation.md) | Document Security and Validation | 1 day | ❌ Not Started | #002 |

**Required For:** FR-2.2, FR-2.3 (Document Upload)

---

### High Priority (Should Have) ⚡

#### EPIC-002: Enrollment Period Management
**Status:** Partially Started (1/5 complete) | **Total Effort:** 4.5 days | **Completed:** 0.5 days

| Ticket | Title | Effort | Status | Dependencies |
|--------|-------|--------|--------|--------------|
| [#007](./TICKET-007-enrollment-period-model-migration.md) | Create Enrollment Period Model | 0.5 day | ✅ COMPLETED | None |
| [#008](./TICKET-008-enrollment-period-crud-backend.md) | Enrollment Period CRUD Backend | 1 day | ❌ Not Started | #007 |
| [#009](./TICKET-009-enrollment-period-ui.md) | Enrollment Period Management UI | 1 day | ❌ Not Started | #008 |
| [#010](./TICKET-010-enrollment-validation-with-periods.md) | Enrollment Validation with Periods | 1 day | ❌ Not Started | #007, #008 |
| [#011](./TICKET-011-auto-period-status-updates.md) | Automatic Period Status Updates | 0.5 day | ❌ Not Started | #007 |

**Required For:** FR-4.6 (Enrollment Period Constraints)

---

#### EPIC-006: Notification System Enhancement
**Status:** Not Started | **Total Effort:** 3 days

| Ticket | Title | Effort | Status | Dependencies |
|--------|-------|--------|--------|--------------|
| [#012](./TICKET-012-notification-preferences-backend.md) | Notification Preferences Backend | 1 day | ❌ Not Started | None |
| [#013](./TICKET-013-notification-center-ui.md) | Notification Center UI | 1.5 days | ❌ Not Started | #012 |
| [#014](./TICKET-014-notification-preferences-ui.md) | Notification Preferences UI | 0.5 day | ❌ Not Started | #012 |

**Required For:** FR-4.3 (Status Notifications), FR-8.4 (Announcements)

---

#### EPIC-007: Audit System Verification
**Status:** Package Installed, Not Implemented | **Total Effort:** 2.5 days

| Ticket | Title | Effort | Status | Dependencies |
|--------|-------|--------|--------|--------------|
| [#015](./TICKET-015-verify-audit-logging-coverage.md) | Verify Audit Logging Coverage | 1 day | ❌ Not Started | None |
| [#016](./TICKET-016-audit-log-viewer-ui.md) | Audit Log Viewer UI | 1.5 days | ❌ Not Started | #015 |

**Required For:** FR-4.4 (Audit Trail), NFR-2.4 (Security)

---

### Medium Priority (Could Have) 📊

#### EPIC-003: Comprehensive Reporting System
**Epic Link:** [EPIC-003](./EPIC-003-reporting-system.md)
**Status:** Not Broken Down | **Estimated:** 4-6 days

**Features:**
- Enrollment statistics reports
- Student demographics reports
- Class roster reports
- Export to PDF/Excel/CSV
- Interactive charts and filtering

#### EPIC-005: System Settings Management
**Epic Link:** [EPIC-005](./EPIC-005-system-settings-management.md)
**Status:** Not Broken Down | **Estimated:** 3-4 days

**Features:**
- Centralized settings database
- Settings management UI
- Settings caching and versioning
- Settings history and audit trail

#### EPIC-008: Permission Management UI
**Epic Link:** [EPIC-008](./EPIC-008-permission-management-ui.md)
**Status:** Not Broken Down | **Estimated:** 3-4 days

**Features:**
- Role management UI
- Permission matrix view
- User role assignment interface
- Permission inheritance display

---

### Low Priority (Future) 🔮

#### EPIC-004: Communication & Announcement System
**Epic Link:** [EPIC-004](./EPIC-004-communication-system.md)
**Status:** Not Broken Down | **Estimated:** 5-7 days

**Features:**
- Announcement broadcasting
- Inquiry/contact forms
- School information display
- Communication history

---

## Implementation Roadmap

### Sprint 1: Critical Features (Week 1-2)
**Focus:** Document Management
- Tickets #001-#006 (5.5 days)
- Goal: Complete document upload and verification workflow

### Sprint 2: High Priority Part 1 (Week 3-4)
**Focus:** Enrollment Periods & Notifications
- Tickets #007-#011 (4.5 days) - Enrollment Periods
- Tickets #012-#014 (3 days) - Notifications
- Goal: Add period management and enhance notifications

### Sprint 3: High Priority Part 2 (Week 5)
**Focus:** Audit System
- Tickets #015-#016 (2.5 days)
- Goal: Complete audit system verification and UI

### Sprint 4: Medium Priority (Week 6-8)
**Focus:** Reporting & Settings (To be broken down)
- EPIC-003: Reporting System
- EPIC-005: System Settings
- EPIC-008: Permission Management UI

### Sprint 5: Polish & Future (Week 9+)
**Focus:** Communication System & Enhancements
- EPIC-004: Communication System
- Additional features and refinements

---

## Ticket Conventions

### Ticket Format
Each ticket includes:
- **Epic Reference:** Link to parent epic
- **Type:** Story, Bug, Task, etc.
- **Priority:** Critical, High, Medium, Low
- **Estimated Effort:** 0.5-2 days
- **Description:** What needs to be done
- **Acceptance Criteria:** Checkboxes for completion
- **Implementation Details:** Code examples and technical specs
- **Testing Requirements:** What to test
- **Dependencies:** Blocking tickets
- **Notes:** Additional context

### Ticket Numbering
- EPIC-001 through EPIC-008: Major features
- TICKET-001+: Implementable stories

### Effort Estimates
- 0.5 day = 4 hours
- 1 day = 8 hours
- 1.5 days = 12 hours
- 2 days = 16 hours

---

## Current Implementation Status

### ✅ Implemented
- User authentication (Spatie Permissions)
- Role-based dashboards (5 user types)
- Student & Guardian CRUD
- Enrollment submission & approval workflow
- Grade level fee management
- Invoice & payment tracking
- Basic email notifications
- Guardian relationship foreign keys (students.guardian_id and enrollments.guardian_id properly reference guardians table)
- **Document model and migration** ⭐ (PR #84)
- **Document upload backend** ⭐ (PR #85)
- **Document upload UI component** ⭐ (PR #86)
- **Document list and management UI** ⭐ (PR #87)
- **EnrollmentPeriod model and migration** ⭐ (PR #88)

### ⚠️ Partially Implemented
- **Document management** (upload complete, verification workflow & security remaining - Tickets #005, #006)
- **Enrollment period management** (model only, no CRUD/UI/validation - Tickets #008-#011)
- **Notification system** (Laravel notifications table only, no custom features)
- **Audit logging** (Spatie Activity Log installed, NOT configured on models)
- Reporting (basic reports, missing exports)
- Permission system (backend complete, missing UI)

### ❌ Missing
- Document verification workflow and security validation
- Enrollment period CRUD, UI, and validation integration
- Notification center UI and preferences
- Audit logging configuration and viewer UI
- Comprehensive reporting with exports
- System settings management UI
- Communication & announcement system

---

## Database Coverage

### Implemented Tables
✅ users, students, guardians, guardian_students (with proper foreign key relationships)
✅ enrollments, grade_level_fees, invoices, invoice_items, payments
✅ roles, permissions, model_has_roles, model_has_permissions
✅ notifications, activity_log
✅ **documents** (fully implemented with upload/management - EPIC-001 PR #84-87 ✅)
✅ **enrollment_periods** (model and migration complete - EPIC-002 PR #88 ✅)

### Missing Tables
❌ system_settings
❌ announcements, inquiries
❌ school_information
❌ notification_preferences

---

## Getting Started

### For Developers

1. **Pick a ticket** from the current sprint
2. **Check dependencies** - ensure required tickets are complete
3. **Review epic** for full context
4. **Follow acceptance criteria** - check off as you complete
5. **Write tests** as specified in ticket
6. **Update ticket status** when complete

### For Project Managers

1. **Prioritize tickets** based on business needs
2. **Assign to developers** based on skills and availability
3. **Track progress** - check acceptance criteria completion
4. **Review dependencies** - ensure logical order
5. **Adjust estimates** based on actual effort

---

## Testing Strategy

Each ticket includes:
- **Unit tests** for models and services
- **Feature tests** for controllers and workflows
- **UI tests** for components and pages
- **Integration tests** for end-to-end flows
- **Accessibility tests** where applicable

Minimum coverage: 60% (enforced by Husky pre-push hook)

---

## Questions?

- **SRS Reference:** See `/home/tony/Desktop/cbhlc/CLAUDE.md`
- **Epic Details:** Read full epic files for context
- **Dependencies:** Check ticket dependencies before starting
- **Blockers:** Escalate if dependencies are blocking progress

---

**Last Updated:** 2025-10-15
**Total Tickets Created:** 16
**Total Epics:** 8
**Estimated Total Effort:** 30-40 days
**Tickets Completed:** 4/16 (25%)

## Recent Bug Fixes & Improvements

### 2025-10-15: Admin New Enrollment Button Fix (PR #94)
- **Issue:** "+ New Enrollment" button on Admin Enrollments Index page was non-functional
- **Fix:**
  - Added Link navigation to button in `enrollment-list.tsx`
  - Implemented `create()` and `store()` methods in `AdminEnrollmentController`
  - Made enrollment form reusable for both admin and guardian roles
- **Impact:**
  - Admin can now create enrollments for any student
  - Automatically associates enrollment with student's guardian
  - Calculates tuition fees based on grade level
  - Enforces business rule for existing students (First quarter auto-enrollment)
- **Tests:** 561 tests passing (2614 assertions), 62.86% coverage
- **Files Changed:** 3 files (controller, components)

### 2025-10-14: Dynamic Content & UI Improvements (PR #90, #92, #93)
- **PR #90:** Dynamic content system implementation
- **PR #92:** Updated sidebar navigation and logo display
- **PR #93:** Fixed test local permission errors
- **Impact:** Enhanced UI consistency and navigation

### 2025-10-13: Admin Dashboard Enrollments Fix (PR #89)
- **Issue:** Admin dashboard enrollments display issues
- **Fix:** Corrected enrollment data flow and display on admin dashboard
- **Tests:** All tests passing

### 2025-10-12: Document Management Complete (PR #84-87)
- **PR #84:** Document model and migration (TICKET-001)
- **PR #85:** Document upload backend implementation (TICKET-002)
- **PR #86:** Document upload UI component (TICKET-003)
- **PR #87:** Document list and management UI (TICKET-004)
- **Impact:**
  - Full document upload functionality for enrollments
  - File validation and secure storage
  - Document listing and management interface
  - 4/6 tickets complete in EPIC-001
- **Tests:** All tests passing with proper coverage

### 2025-10-12: Enrollment Period Management (PR #88)
- **PR #88:** EnrollmentPeriod model and migration (TICKET-007)
- **Impact:** Foundation for enrollment period constraints
- **Status:** 1/5 tickets complete in EPIC-002
- **Tests:** All tests passing

### 2025-10-11: Guardian Relationship Foreign Keys (PR #81)
- **Issue:** `students.guardian_id` and `enrollments.guardian_id` were incorrectly referencing `users` table
- **Fix:** Created migrations to update foreign keys to properly reference `guardians` table
- **Impact:**
  - Updated EnrollmentFactory to return Guardian model IDs
  - Fixed EnrollmentController to use Guardian model when creating enrollments
  - Updated 8 test files (100+ assertions) to use correct Guardian model IDs
  - All 520 tests passing
- **Commits:** 8 commits merged
- **Files Changed:** 29 files (models, controllers, services, factories, migrations, tests)
