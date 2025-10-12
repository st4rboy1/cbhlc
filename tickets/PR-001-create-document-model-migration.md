# PR #001: Create Document Model and Migration

## Related Ticket

[TICKET-001: Create Document Model and Migration](./TICKET-001-create-document-model-migration.md)

## Epic

[EPIC-001: Document Management System](./EPIC-001-document-management-system.md)

## Description

This PR implements the database foundation for the document management system by creating the `documents` table migration and the `Document` Eloquent model with proper relationships and enums.

## Changes Made

### Database Migration

- ✅ Created `create_documents_table.php` migration
- ✅ Added `documents` table with all required fields
- ✅ Implemented foreign key constraints for `student_id` and `verified_by`
- ✅ Added enum fields for `document_type` and `verification_status`
- ✅ Added indexes for performance optimization

### Model

- ✅ Created `app/Models/Document.php`
- ✅ Defined `belongsTo(Student::class)` relationship
- ✅ Defined `belongsTo(User::class, 'verified_by')` relationship
- ✅ Configured casts for `upload_date` and `verified_at` timestamps
- ✅ Set up fillable attributes

### Updated Models

- ✅ Added `hasMany(Document::class)` relationship to `Student` model

## Type of Change

- [x] New feature (database migration)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Migration Tests

- [ ] Migration runs successfully: `php artisan migrate`
- [ ] Migration rollback works: `php artisan migrate:rollback`
- [ ] Fresh migration works: `php artisan migrate:fresh`
- [ ] Can create document record in database

### Model Tests

- [ ] Document model can be instantiated
- [ ] `student()` relationship returns correct student
- [ ] `verifiedBy()` relationship returns correct user
- [ ] Casts work correctly (dates, enums)
- [ ] Mass assignment protection works

### Database Structure Tests

- [ ] Foreign key constraints work (cascade delete)
- [ ] Enum values are restricted correctly
- [ ] Default values are set correctly
- [ ] Indexes exist and improve query performance

## Verification Steps

```bash
# Run migration
./vendor/bin/sail artisan migrate

# Test in tinker
./vendor/bin/sail artisan tinker
>>> $student = Student::first();
>>> $doc = $student->documents()->create([
    'document_type' => 'birth_certificate',
    'original_filename' => 'test.jpg',
    'stored_filename' => 'abc123.jpg',
    'file_path' => 'documents/1/abc123.jpg',
    'file_size' => 12345,
    'mime_type' => 'image/jpeg',
    'upload_date' => now(),
]);
>>> $doc->student->full_name; // Should return student name
>>> $doc->verification_status; // Should return 'pending'

# Rollback test
./vendor/bin/sail artisan migrate:rollback

# Run tests
./vendor/bin/sail pest tests/Unit/Models/DocumentTest.php
./vendor/bin/sail pest tests/Feature/Database/DocumentMigrationTest.php
```

## Dependencies

- None (This is the first ticket in the document management epic)

## Breaking Changes

None

## Rollback Plan

```bash
php artisan migrate:rollback
```

## Screenshots

N/A (Database migration)

## Documentation

- Updated `Student` model docblock to include `documents()` relationship
- Added inline comments in migration for clarity

## Deployment Notes

- Run migrations on deployment: `php artisan migrate`
- No data migration needed (new table)
- No downtime expected

## Post-Merge Checklist

- [ ] Migration run successfully on staging
- [ ] Migration run successfully on production
- [ ] Document model accessible in codebase
- [ ] No errors in production logs
- [ ] Next ticket (TICKET-002) can begin

## Reviewer Notes

Please verify:

1. Migration follows Laravel naming conventions
2. Enum values match SRS requirements (birth_certificate, report_card, form_138, good_moral_certificate, other)
3. Foreign key constraints are correct
4. Indexes are appropriate for query patterns
5. Model relationships are bidirectional (Student ↔ Document)

---

**Ticket:** #001
**Estimated Effort:** 0.5 day
**Actual Effort:** _[To be filled after completion]_
