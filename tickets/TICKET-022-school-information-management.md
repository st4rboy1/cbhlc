# TICKET-022: School Information Management

## Epic

[EPIC-004: Communication and Announcement System](./EPIC-004-communication-system.md)

## Priority

Low (Won't Have Initially)

## User Story

As a super administrator, I want to manage school contact information and office hours in a centralized location so that this information can be displayed consistently across the website.

## Related SRS Requirements

- **FR-8.2:** System shall display school contact information
- **FR-8.3:** System shall show school office hours

## Description

Implement a school information management system allowing administrators to maintain contact details, office hours, social media links, and other school information through a simple key-value interface with grouping.

## Acceptance Criteria

- ✅ Super Admin can view all school information
- ✅ Super Admin can update school information
- ✅ Information grouped logically (contact, hours, social, etc.)
- ✅ Different field types supported (text, email, phone, time, JSON)
- ✅ Public contact page displays school information
- ✅ Changes take effect immediately
- ✅ Information cached for performance

## Technical Requirements

### Database

1. Create `create_school_information_table.php` migration
2. Columns: key (unique), value, type, group, label, description
3. Seed with default information

### Backend

1. Create `SchoolInformation` model
2. Create `SuperAdmin/SchoolInformationController.php`
3. Methods: index(), update()
4. Create `Public/ContactController.php` for display

### Frontend

1. Create `/resources/js/pages/super-admin/school-information/index.tsx`
2. Create `/resources/js/pages/public/contact.tsx`
3. Form with grouped fields
4. Public display of contact information

## Routes

```php
Route::prefix('super-admin/school-information')
    ->name('super-admin.school-information.')
    ->middleware('role:super_admin')
    ->group(function () {
        Route::get('/', [SchoolInformationController::class, 'index'])->name('index');
        Route::put('/', [SchoolInformationController::class, 'update'])->name('update');
    });

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
```

## Dependencies

- None

## Estimated Effort

**1 day**

## Notes

- Use caching for public information display
- Consider validating email and phone formats
- Office hours may need JSON structure for different days
